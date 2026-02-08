#!/usr/bin/env python3
"""
Script to generate bcrypt password hash equivalent to PHP's password_hash() function.
This generates the same format as PHP's PASSWORD_DEFAULT (which uses bcrypt).
"""

import bcrypt
import sys
import os
from pathlib import Path


def generate_password_hash(password, cost=10):
    """
    Generate a bcrypt hash for the given password with the specified cost.
    
    Args:
        password (str): The plain text password to hash
        cost (int): The cost factor for bcrypt (default 10, same as PHP's default)
        
    Returns:
        str: The bcrypt hash in the format used by PHP's password_hash()
    """
    # Convert password to bytes if it's a string
    if isinstance(password, str):
        password = password.encode('utf-8')
    
    # Generate the bcrypt hash with the specified cost
    hashed = bcrypt.hashpw(password, bcrypt.gensalt(rounds=cost))
    
    # Return the hash as a string (it's already in the correct format)
    return hashed.decode('utf-8')


def create_sql_file(username, password_hash):
    """
    Create SQL file with admin user insertion.
    
    Args:
        username (str): The admin username
        password_hash (str): The bcrypt password hash
    """
    # Create sql directory if it doesn't exist
    sql_dir = Path('./sql')
    sql_dir.mkdir(exist_ok=True)
    
    # SQL content
    sql_content = f"""-- Admin user insertion
-- Generated bcrypt hash for secure password storage

INSERT INTO admins (username, password_hash) VALUES ('{username}', '{password_hash}');
"""
    
    # Write to file
    sql_file_path = sql_dir / 'admin.sql'
    with open(sql_file_path, 'w', encoding='utf-8') as f:
        f.write(sql_content)
    
    return sql_file_path


def main():
    # Default values
    default_username = 'admin'
    default_password = 'admin123'
    
    # Get username and password from command line arguments
    username = sys.argv[1] if len(sys.argv) > 1 else default_username
    password = sys.argv[2] if len(sys.argv) > 2 else default_password
    
    print(f"Generating bcrypt hash for user: '{username}'")
    print(f"Password: '{password}'")
    
    # Generate hash
    hash_result = generate_password_hash(password, 10)
    print(f"Generated hash: {hash_result}")
    
    # Create SQL file
    sql_file_path = create_sql_file(username, hash_result)
    print(f"\n✓ SQL file created successfully: {sql_file_path.absolute()}")
    print(f"\nSQL content:")
    print(f"INSERT INTO admins (username, password_hash) VALUES ('{username}', '{hash_result}');")


if __name__ == "__main__":
    main()