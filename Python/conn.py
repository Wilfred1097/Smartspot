import mysql.connector
from mysql.connector import Error

# Function to create a MySQL connection
def create_connection():
    try:
        # Replace the following credentials with your XAMPP MySQL setup
        connection = mysql.connector.connect(
            host='localhost',
            user='root',
            password='',
            database='smartspot'
        )

        if connection.is_connected():
            return connection

    except Error as e:
        print(f"Error: {e}")
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
