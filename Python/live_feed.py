from flask import Flask, Response, redirect, url_for
import cv2
import pandas as pd
import numpy as np
import json
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

# Open the video capture
# camera = cv2.VideoCapture("rtsp://admin2024:Wilfred.912816@192.168.8.108:554/stream1")
camera = cv2.VideoCapture(0)  # Uncomment for local camera

# Function to update the live_feed_url in the database
def update_live_feed_url(url):
    connection = create_connection()
    if connection:
        try:
            cursor = connection.cursor()
            update_query = """UPDATE config SET live_feed_url=%s WHERE id=1"""
            cursor.execute(update_query, (url,))
            connection.commit()
            print(f"Database updated with live feed URL: {url}")
        except Error as e:
            print(f"Error updating database: {e}")
        finally:
            close_connection(connection)

def generate_frames():
    # # Initialize variables to track previous values of vacant and occupied spaces
    previous_vacant_spaces = None
    previous_occupied_spaces = None
    while True:
        success, frame = camera.read()
        if not success:
            break

        frame = cv2.resize(frame, (1020, 500))

        # Run YOLO model on the frame
        results = model.predict(frame)

        # Extract predictions and convert to dataframe
        a = results[0].boxes.data
        px = pd.DataFrame(a).astype("float")

        # Dictionary to keep track of the car count in each area
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

        # Draw polygons for each parking area and color them based on whether a vehicle is present
        for area_id, area_coords in areas.items():
            color = (0, 0, 255) if area_car_count[area_id] > 0 else (0, 255, 0)  # Red if occupied, green if vacant
            cv2.polylines(frame, [np.array(area_coords, np.int32)], True, color, 1)
            cv2.putText(frame, str(area_id), (area_coords[0][0], area_coords[0][1] - 10), cv2.FONT_HERSHEY_COMPLEX, 0.4, color, 1)

        # Calculate total and occupied spaces
        total_spaces = len(areas)
        occupied_spaces = sum(1 for count in area_car_count.values() if count > 0)
        vacant_spaces = total_spaces - occupied_spaces

        # Display vacant and occupied counts on the video
        # cv2.putText(frame, f'Vacant: {vacant_spaces}', (15, 20), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
        # cv2.putText(frame, f'Occupied: {occupied_spaces}', (15, 55), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)

        # Update database only if there is a change in vacant or occupied spaces
        if vacant_spaces != previous_vacant_spaces or occupied_spaces != previous_occupied_spaces:
            connection = create_connection()
            if connection:
                try:
                    cursor = connection.cursor()
                    update_query = """UPDATE config SET vacant_space=%s, occupied_space=%s WHERE id=1"""
                    cursor.execute(update_query, (vacant_spaces, occupied_spaces))
                    connection.commit()
                    print("Database updated with vacant and occupied spaces.")
                except Error as e:
                    print(f"Error updating database: {e}")
                finally:
                    close_connection(connection)
            
            # Update the previous values
            previous_vacant_spaces = vacant_spaces
            previous_occupied_spaces = occupied_spaces

        # Encode frame as JPEG
        ret, buffer = cv2.imencode('.jpg', frame)
        frame = buffer.tobytes()
        
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')

@app.route('/')
def index():
    return redirect(url_for('video_feed'))

@app.route('/video_feed')
def video_feed():
    return Response(generate_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

if __name__ == "__main__":

    # Get the live feed URL
    live_feed_url = "http://192.168.8.113:5000"

    # Update the live feed URL in the database
    update_live_feed_url(live_feed_url)

    app.run(host='0.0.0.0', port=5000)

    # Release resources
    camera.release()
    cv2.destroyAllWindows()
