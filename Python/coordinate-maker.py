import cv2
import json
import tkinter as tk
import tkinter.messagebox
import numpy as np
from conn import create_connection, close_connection

# Initialize a dictionary to store coordinates
clicked_coordinates = {}

# Define a counter for coordinate sets
coordinate_counter = 1

# Path to the JSON file
json_file_path = "parking_areas.json"

# Initialize global variables for the video capture
cap = None
frame = None

# Load existing parking areas from JSON file
def load_parking_areas():
    try:
        with open(json_file_path, "r") as json_file:
            return json.load(json_file)
    except FileNotFoundError:
        return {}  # Return an empty dictionary if the file doesn't exist

# Save updated parking areas to JSON file
def save_parking_areas(areas):
    with open(json_file_path, "w") as json_file:
        json.dump(areas, json_file, indent=4)

# Function to create and display the Tkinter window with coordinates and input field
def display_coordinates_window(coords):
    window = tk.Tk()
    window.title("Save Coordinate")
    window.resizable(False, False)

    # Center the window on the screen
    screen_width = window.winfo_screenwidth()
    screen_height = window.winfo_screenheight()
    window_width = 420
    window_height = 125
    x = (screen_width // 2) - (window_width // 2)
    y = (screen_height // 2) - (window_height // 2)
    window.geometry(f"{window_width}x{window_height}+{x}+{y}")

    # Display the coordinates
    tk.Label(window, text="Coordinates:", font=("Helvetica 12 bold")).place(x=5, y=10)
    tk.Label(window, text=f"({coords})", font=("Helvetica 11")).place(x=110, y=10)
    
    # Input field
    tk.Label(window, text="Parking Space Number:", font=("Helvetica 12 bold")).place(x=5, y=35)
    entry = tk.Entry(window, font=("Helvetica 11"))
    entry.focus()
    entry.place(x=210, y=35)

    # Submit button
    def on_submit():
        value = entry.get()
        if not value.strip():  # Check if the input is blank
            tk.messagebox.showerror("Input Error", "Please enter a number.")
        elif not value.isdigit():  # Check if the input is not a number
            tk.messagebox.showerror("Input Error", "Please enter a valid number.")
            entry.delete(0, tk.END)
        else:
            parking_number = int(value)
            areas = load_parking_areas()

            # Check if parking space number already exists
            if str(parking_number) in areas:
                response = tk.messagebox.askyesno("Update Confirmation", 
                    f"Parking space number {parking_number} already exists. Do you want to update the coordinates?")
                if response:
                    areas[str(parking_number)] = coords
                    save_parking_areas(areas)
                    tk.messagebox.showinfo("Update Successful", f"Parking Space number {parking_number} updated successfully.")
                    # Update the database entry
                    try:
                        conn = create_connection()
                        cursor = conn.cursor()
                        # Delete old record and insert new
                        cursor.execute("DELETE FROM `parking_status` WHERE `slots_number` = %s", (parking_number,))
                        cursor.execute(
                            "INSERT INTO `parking_status` (`slots_number`, `status`) VALUES (%s, %s)",
                            (parking_number, 0)
                        )
                        conn.commit()
                    except Exception as e:
                        tk.messagebox.showerror("Database Error", f"An error occurred: {e}")
                    finally:
                        close_connection(conn)
                    window.destroy()
                    refresh_frame()
            else:
                areas[str(parking_number)] = coords
                save_parking_areas(areas)
                tk.messagebox.showinfo("Save Successful", f"Parking Space number {parking_number} saved successfully.")
                # Insert into the database
                try:
                    conn = create_connection()
                    cursor = conn.cursor()
                    cursor.execute(
                        "INSERT INTO `parking_status` (`slots_number`, `status`) VALUES (%s, %s)",
                        (parking_number, 0)
                    )
                    conn.commit()
                except Exception as e:
                    tk.messagebox.showerror("Database Error", f"An error occurred: {e}")
                finally:
                    close_connection(conn)
                window.destroy()
                refresh_frame()
            
    tk.Button(window, text="Submit", command=on_submit, font=("Helvetica 12 bold")).place(x=130, y=75)
    tk.Button(window, text="Cancel", command=window.destroy, font=("Helvetica 12 bold")).place(x=220, y=75)

    # Start the Tkinter event loop
    window.mainloop()

def display_delete_window():
    window = tk.Tk()
    window.title("Delete Parking Number Coordinate")
    window.resizable(False, False)

    # Center the window on the screen
    screen_width = window.winfo_screenwidth()
    screen_height = window.winfo_screenheight()
    window_width = 370
    window_height = 105
    x = (screen_width // 2) - (window_width // 2)
    y = (screen_height // 2) - (window_height // 2)
    window.geometry(f"{window_width}x{window_height}+{x}+{y}")

    # Input field
    tk.Label(window, text="Parking Space Number to Delete:", font=("Helvetica 11 bold")).place(x=35, y=10)
    entry = tk.Entry(window, font=("Helvetica 11"), width=5)
    entry.focus()
    entry.place(x=280, y=10)

    # Submit button
    def on_submit():
        value = entry.get()
        if not value.strip():  # Check if the input is blank
            tk.messagebox.showerror("Input Error", "Please enter a number.")
        elif not value.isdigit():  # Check if the input is not a number
            tk.messagebox.showerror("Input Error", "Please enter a valid number.")
            entry.delete(0, tk.END)
        else:
            parking_number = int(value)
            areas = load_parking_areas()
            
            # Check if parking space number exists
            if str(parking_number) in areas:
                # Delete from areas
                del areas[str(parking_number)]
                save_parking_areas(areas)
                
                # Delete from database
                try:
                    conn = create_connection()
                    cursor = conn.cursor()
                    cursor.execute("DELETE FROM `parking_status` WHERE `slots_number` = %s", (parking_number,))
                    conn.commit()
                except Exception as e:
                    tk.messagebox.showerror("Database Error", f"An error occurred: {e}")
                finally:
                    close_connection(conn)
                
                # Notify user and refresh
                tk.messagebox.showinfo("Delete Successful", f"Parking Space number {parking_number} deleted successfully.")
                entry.delete(0, tk.END)
                refresh_frame()
            else:
                tk.messagebox.showerror("Not Found", f"Parking Space number {parking_number} does not exist.")

    tk.Button(window, text="Delete", command=on_submit, font=("Helvetica 12 bold")).place(x=90, y=55)
    tk.Button(window, text="Cancel", command=window.destroy, font=("Helvetica 12 bold")).place(x=180, y=55)

    # Start the Tkinter event loop
    window.mainloop()

def refresh_frame():
    global frame, cap
    # Capture frame from the RTSP stream
    ret, frame = cap.read()

    if ret:
        # Resize the frame if needed
        frame = cv2.resize(frame, (1020, 500))
        
        # Draw the polygons for each area from the JSON file
        areas = load_parking_areas()
        for area_id, area_coords in areas.items():
            centroid = get_centroid(area_coords)
            cv2.polylines(frame, [np.array(area_coords, np.int32)], True, (0, 255, 0), 1)
            cv2.putText(frame, str(area_id), centroid, cv2.FONT_HERSHEY_COMPLEX, 0.5, (0, 255, 0), 1)
        
        # Display the result
        cv2.imshow("SmartSpot", frame)

# Function to calculate the centroid of a polygon
def get_centroid(area_coords):
    area_coords = np.array(area_coords)
    moments = cv2.moments(area_coords)
    if moments['m00'] != 0:
        cx = int(moments['m10'] / moments['m00'])
        cy = int(moments['m01'] / moments['m00'])
        return (cx, cy)
    else:
        return np.mean(area_coords, axis=0).astype(int)

# Define the mouse callback function to get the SmartSpot values on mouse movement
def getCoordinate(event, x, y, flags, param):
    global clicked_coordinates, coordinate_counter
    if event == cv2.EVENT_LBUTTONDOWN:  # Record coordinates on mouse click
        if coordinate_counter not in clicked_coordinates:
            clicked_coordinates[coordinate_counter] = []
        clicked_coordinates[coordinate_counter].append([x, y])  # Append the coordinate as a list
        
        # Check if we have collected 4 coordinates
        if len(clicked_coordinates[coordinate_counter]) == 4:
            # Format and print the coordinates as a JSON-like structure
            formatted_output = {str(coordinate_counter): clicked_coordinates[coordinate_counter]}
            
            # Create and display the Tkinter window with coordinates and input field
            display_coordinates_window(clicked_coordinates[coordinate_counter])
            
            coordinate_counter += 1  # Move to the next coordinate set
            # Prepare for new coordinates
            clicked_coordinates[coordinate_counter] = []

# Load areas from JSON file
areas = load_parking_areas()

# Set up video capture from the RTSP stream
# cap = cv2.VideoCapture('rtsp://admin2024:Wilfred.912816@192.168.8.108:554/stream1')
# cap = cv2.VideoCapture('rtsp://admin:Wilfred1234@192.168.8.100:554/onvif1')
cap = cv2.VideoCapture(0)

# Set up the window and callback
cv2.namedWindow('SmartSpot', cv2.WINDOW_NORMAL)
cv2.setMouseCallback('SmartSpot', getCoordinate)

# Main loop to display frames from the video stream
while True:
    refresh_frame()

    # Wait for the user to press 'q' to exit
    key = cv2.waitKey(1) & 0xFF
    if key == ord('q'):
        break
    elif key == ord('Q'):
        break
    elif key == ord('d'):
        display_delete_window()
    elif key == ord('D'):
        display_delete_window()

# Release resources
cv2.destroyAllWindows()