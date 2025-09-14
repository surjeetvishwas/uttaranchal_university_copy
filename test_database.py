#!/usr/bin/env python3
import sqlite3
import os

# Create a simple SQLite database to test with
db_path = 'mapping.db'

# Remove existing database if it exists
if os.path.exists(db_path):
    os.remove(db_path)

# Create database and tables
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

# Create students table
cursor.execute('''
    CREATE TABLE students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        roll_number TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
''')

# Create results table
cursor.execute('''
    CREATE TABLE results (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        roll_number TEXT NOT NULL,
        semester INTEGER NOT NULL,
        file_path TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (roll_number) REFERENCES students (roll_number)
    )
''')

# Insert test data
test_students = [
    ('1234567890', 'John Doe'),
    ('0987654321', 'Jane Smith'),
    ('1122334455', 'Alice Johnson'),
    ('5544332211', 'Bob Wilson')
]

for roll, name in test_students:
    cursor.execute('INSERT INTO students (roll_number, name) VALUES (?, ?)', (roll, name))

# Insert test results
test_results = [
    ('1234567890', 1, 'UUD/results/1234567890_sem1.pdf'),
    ('1234567890', 2, 'UUD/results/1234567890_sem2.pdf'),
    ('1234567890', 3, 'UUD/results/1234567890_sem3.pdf'),
    ('0987654321', 1, 'UUD/results/0987654321_sem1.pdf'),
    ('0987654321', 2, 'UUD/results/0987654321_sem2.pdf'),
    ('1122334455', 1, 'UUD/results/1122334455_sem1.pdf'),
]

for roll, semester, file_path in test_results:
    cursor.execute('INSERT INTO results (roll_number, semester, file_path) VALUES (?, ?, ?)', 
                   (roll, semester, file_path))

conn.commit()

# Display the data
print("=== TEST DATABASE CREATED ===")
print("\nStudents:")
cursor.execute('SELECT roll_number, name FROM students')
for row in cursor.fetchall():
    print(f"Roll: {row[0]}, Name: {row[1]}")

print("\nResults:")
cursor.execute('SELECT roll_number, semester, file_path FROM results')
for row in cursor.fetchall():
    print(f"Roll: {row[0]}, Semester: {row[1]}, File: {row[2]}")

conn.close()
print(f"\nDatabase created at: {os.path.abspath(db_path)}")
print("\nTest Cases:")
print("1. Roll: 1234567890, Name: John Doe (has results for sem 1,2,3)")
print("2. Roll: 0987654321, Name: Jane Smith (has results for sem 1,2)")
print("3. Roll: 1122334455, Name: Alice Johnson (has result for sem 1 only)")
print("4. Roll: 5544332211, Name: Bob Wilson (no results)")