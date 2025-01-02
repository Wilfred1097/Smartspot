from flask import Flask, Response, redirect, url_for
import cv2
import pandas as pd
import numpy as np
import json
import time
from ultralytics import YOLO
from conn import create_connection, close_connection

app = Flask(__name__)

# Load the YOLO model
model = YOLO('best-old.pt')

# Load areas from JSON file
with open("parking_areas.json", "r") as json_file:
    areas = json.load(json_file)

# Convert area keys from string to int and values to tuples
areas = {int(k): [tuple(map(int, v)) for v in v_list] for k, v_list in areas.items()}

# Open the video capture (local webcam or RTSP stream)
cap = cv2.VideoCapture(0)  # Replace with your RTSP URL if needed

# Set the time interval for detection (in seconds)
detection_interval = 5
last_detection_time = time.time()  # Store the current time when the app starts

def generate_frames():
    global last_detection_time

    # Initialize area_car_count to prevent UnboundLocalError
    area_car_count = {key: 0 for key in areas}

    while True:
        ret, frame = cap.read()
        if not ret:
            break
        frame = cv2.resize(frame, (1020, 500))

        # Check if 10 seconds have passed since the last detection
        current_time = time.time()
        if current_time - last_detection_time >= detection_interval:
            # Run YOLO model on the frame if the time interval has passed
            results = model.predict(frame)

            # Extract predictions and convert to dataframe
            a = results[0].boxes.data
            px = pd.DataFrame(a).astype("float")

            # Reset car count for each area before detecting
            area_car_count = {key: 0 for key in areas}

            for index, row in px.iterrows():
                x1 = int(row[0])
                y1 = int(row[1])
                x2 = int(row[2])
                y2 = int(row[3])
                class_id = int(row[5])  # Class index

                # Calculate the center of the detected object
                cx = int(x1 + x2) // 2
                cy = int(y1 + y2) // 2

                # Check if the object is inside any of the defined parking areas
                for area_id, area_coords in areas.items():
                    result = cv2.pointPolygonTest(np.array(area_coords, np.int32), (cx, cy), False)
                    if result >= 0:  # If the object is inside the area
                        area_car_count[area_id] += 1
                        cv2.circle(frame, (cx, cy), 3, (0, 0, 255), -1)

            # Update parking_status table for all slots
            connection = create_connection()
            if connection:
                try:
                    cursor = connection.cursor()
                    update_queries = []

                    # Iterate over area_car_count to collect updates
                    for area_id, count in area_car_count.items():
                        status = 1 if count > 0 else 0
                        update_queries.append((status, area_id))

                    # Execute all updates in one batch
                    if update_queries:
                        update_query = """UPDATE parking_status SET status = %s WHERE slots_number = %s"""
                        cursor.executemany(update_query, update_queries)
                        connection.commit()
                except Exception as e:
                    print(f"Error updating parking_status table: {e}")
                finally:
                    close_connection(connection)

            # Update the last detection time
            last_detection_time = current_time

        # Draw polygons for each parking area and color them based on vehicle presence
        for area_id, area_coords in areas.items():
            color = (0, 0, 255) if area_car_count[area_id] > 0 else (0, 255, 0)  # Red if occupied, green if vacant
            cv2.polylines(frame, [np.array(area_coords, np.int32)], True, color, 1)
            cv2.putText(frame, str(area_id), (area_coords[0][0], area_coords[0][1] - 10), cv2.FONT_HERSHEY_COMPLEX, 0.4, color, 1)

        # Encode the frame as a JPEG image
        ret, buffer = cv2.imencode('.jpg', frame)
        frame = buffer.tobytes()

        # Stream the frame
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')

@app.route('/')
def index():
    return redirect(url_for('video_feed'))

@app.route('/video_feed')
def video_feed():
    return Response(generate_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000)

    # Release resources when the app stops
    cap.release()
    cv2.destroyAllWindows()
