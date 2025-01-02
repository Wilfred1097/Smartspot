import tkinter as tk
import subprocess
from tkinter import filedialog, messagebox
import shutil
import mysql.connector
from mysql.connector import Error
import os
from conn import create_connection, close_connection
import re

def load_db_config():
    """Read database configuration from conn.py and return as a dictionary."""
    config = {
        "host": "",
        "user": "",
        "password": "",
        "database": ""
    }
    try:
        with open('conn.py', 'r') as file:
            content = file.read()
            # Use regex to find the values for each parameter in conn.py
            config["host"] = re.search(r"host='(.*?)'", content).group(1)
            config["user"] = re.search(r"user='(.*?)'", content).group(1)
            config["password"] = re.search(r"password='(.*?)'", content).group(1)
            config["database"] = re.search(r"database='(.*?)'", content).group(1)
    except (FileNotFoundError, AttributeError) as e:
        print(f"Error loading configuration: {e}")
    return config

def center_window(window, width, height):
    # Get the screen width and height
    screen_width = window.winfo_screenwidth()
    screen_height = window.winfo_screenheight()

    # Calculate the x and y coordinates to position the window in the center
    x = (screen_width // 2) - (width // 2)
    y = (screen_height // 2) - (height // 2)

    # Set the position of the window
    window.geometry(f'{width}x{height}+{x}+{y}')

def open_coordinate_maker():
    # Run the external script that contains the OpenCV code
    try:
        subprocess.Popen(["python", "coordinate-maker.py"])
    except Exception as e:
        print(f"Error launching OpenCV script: {e}")

def preview_live_feed():
    # Run the external script that contains the OpenCV code
    try:
        subprocess.Popen(["python", "live_feed_preview.py"])
    except Exception as e:
        print(f"Error launching OpenCV script: {e}")

def open_live_feed():
    # Run the external script that contains the OpenCV code
    try:
        subprocess.Popen(["python", "live_feed.py"])
    except Exception as e:
        print(f"Error launching OpenCV script: {e}")

def configure_database():
    # Load the current configuration from conn.py
    db_config = load_db_config()

    # Create a new top-level window
    db_config_window = tk.Toplevel(root)
    db_config_window.title("Database Configuration")
    db_config_window.geometry("300x170")
    db_config_window.grab_set()  # Make the dialog modal

    # Create and place labels and entry fields for database configuration
    tk.Label(db_config_window, text="Host:", font=("Arial", 10)).place(x=5, y=5)
    db_host_entry = tk.Entry(db_config_window, font=("Arial", 10), width=30)
    db_host_entry.insert(0, db_config.get("host", ""))  # Populate with existing value
    db_host_entry.place(x=80, y=5)

    tk.Label(db_config_window, text="Username:", font=("Arial", 10)).place(x=5, y=30)
    db_user_entry = tk.Entry(db_config_window, font=("Arial", 10), width=30)
    db_user_entry.insert(0, db_config.get("user", ""))  # Populate with existing value
    db_user_entry.place(x=80, y=30)

    tk.Label(db_config_window, text="Password:", font=("Arial", 10)).place(x=5, y=55)
    # db_pass_entry = tk.Entry(db_config_window, show="*", font=("Arial", 10), width=30)
    db_pass_entry = tk.Entry(db_config_window, font=("Arial", 10), width=30)
    db_pass_entry.insert(0, db_config.get("password", ""))  # Populate with existing value
    db_pass_entry.place(x=80, y=55)

    tk.Label(db_config_window, text="DB Name:", font=("Arial", 10)).place(x=5, y=80)
    db_name_entry = tk.Entry(db_config_window, font=("Arial", 10), width=30)
    db_name_entry.insert(0, db_config.get("database", ""))  # Populate with existing value
    db_name_entry.place(x=80, y=80)

    # Create a function to handle the OK button click
    def test_connection_and_save():
        db_host = db_host_entry.get()
        db_user = db_user_entry.get()
        db_pass = db_pass_entry.get()
        db_name = db_name_entry.get()

        # Test the connection
        try:
            connection = mysql.connector.connect(
                host=db_host,
                user=db_user,
                password=db_pass,
                database=db_name
            )

            if connection.is_connected():
                print("Database connected successfully!")

                # Now proceed with generating the Python content
                python_content = f"""import mysql.connector
from mysql.connector import Error

# Function to create a MySQL connection
def create_connection():
    try:
        # Replace the following credentials with your XAMPP MySQL setup
        connection = mysql.connector.connect(
            host='{db_host}',
            user='{db_user}',
            password='{db_pass}',
            database='{db_name}'
        )

        if connection.is_connected():
            return connection

    except Error as e:
        print(f"Error: {{e}}")
        return None

# Function to close the connection
def close_connection(connection):
    if connection.is_connected():
        connection.close()

# Main program
if __name__ == "__main__":
    conn = create_connection()
    if conn:
        # Close the connection
        close_connection(conn)
"""

                # Write the configuration to conn.py
                script_dir = os.path.dirname(os.path.abspath(__file__))
                python_file_path = os.path.join(script_dir, 'conn.py')

                try:
                    with open(python_file_path, 'w') as python_file:
                        python_file.write(python_content)
                    messagebox.showinfo("Configuration Saved", "Database configuration saved successfully!")
                except Exception as e:
                    messagebox.showerror("Save Error", f"Error saving configuration: {e}")

                db_config_window.destroy()

        except Error as e:
            messagebox.showerror("Connection Error", f"Error: Unable to connect to the database. {e}")

    # Add OK and Cancel buttons
    tk.Button(db_config_window, text="Save", command=test_connection_and_save, font=("Arial", 10), relief="ridge", width=10).place(x=45, y=120)
    tk.Button(db_config_window, text="Cancel", command=db_config_window.destroy, font=("Arial", 10), relief="ridge", width=10).place(x=160, y=120)

def update_rtsp_url():
    rtsp_url = entry.get()
    connection = create_connection()
    
    if connection:
        try:
            cursor = connection.cursor()
            update_query = "UPDATE config SET rstp_url=%s WHERE id=1"
            cursor.execute(update_query, (rtsp_url,))
            connection.commit()
            messagebox.showinfo("Success", "RTSP URL updated successfully!")
        except Error as e:
            messagebox.showerror("Error", f"Failed to update RTSP URL: {e}")
        finally:
            close_connection(connection)

# Create the main application window
root = tk.Tk()
root.title("Smart Spot")  # Set the window title
root.resizable(False, False)

# Define the width and height of the window
window_width = 360
window_height = 130

# Load the PNG icon
icon_path = "ss-favicon.png"  # Replace with the actual path to your PNG file
icon = tk.PhotoImage(file=icon_path)

root.iconphoto(False, icon)  # False sets it as the main window icon

# Center the window on the screen
center_window(root, window_width, window_height)

# Create a label widget for the RTSP URL
label = tk.Label(root, text="RTSP URL:", font=("Arial", 10))
label.place(x=10, y=70)

# Create a text input field next to the label
entry = tk.Entry(root, font=("Arial", 10))
entry.place(x=5, y=95, width=350)  # Adjust the x, y, and width as needed

# Button to save the RTSP URL
button3 = tk.Button(root, text="Save", command=update_rtsp_url, font=("Arial", 9), width=8, activebackground="gray", activeforeground="white", relief="groove")
button3.place(x=288, y=65)

# Create button widgets
button1 = tk.Button(root, text="Open Coordinate Maker", command=open_coordinate_maker, font=("Arial", 10), width=23, activebackground="gray", activeforeground="white", relief="groove")
button1.place(x=160, y=5)
button1 = tk.Button(root, text="Preview Live Feed", command= preview_live_feed, font=("Arial", 10), width=18, activebackground="gray", activeforeground="white", relief="groove")
button1.place(x=5, y=5)
button2 = tk.Button(root, text="Start Live Feed", command=open_live_feed, font=("Arial", 10), width=18, activebackground="gray", activeforeground="white", relief="groove")
button2.place(x=5, y=35)
button3 = tk.Button(root, text="Configure Database Connection", command=configure_database, font=("Arial", 10), width=23, activebackground="gray", activeforeground="white", relief="groove")
button3.place(x=160, y=35)

# Start the Tkinter event loop
root.mainloop()
