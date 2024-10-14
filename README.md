
![OIG2-removebg-preview](https://github.com/user-attachments/assets/889670bb-26c7-4099-9e21-83188fbc8964)

# Stoic-NCIC

**Stoic-NCIC** is a law enforcement-style database system designed to simulate the core features of an NCIC database. It provides tools for managing individual records, criminal records, vehicle reports, warrants, missing persons, and stolen property. The system includes robust search functionality and role management to control user access.

## Features

### 1. Database Structure
- **Individuals**: Store basic personal information including name, date of birth (DOB), gender, and more.
- **Criminal Records**: Track arrests, charges, and convictions.
- **Vehicle Records**: Manage registered vehicles and reports for stolen vehicles (excluding license plates).
- **Warrants**: Store information on active warrants.
- **Missing Persons**: Log reports for missing individuals.
- **Stolen Property**: Report stolen items such as firearms or other valuables.

### 2. Search Functions
- **Quick Lookups**: Perform quick searches by name, social security number, or other identifying details.
- **Multi-Query**: Run comprehensive searches across multiple record types, including individuals, vehicles, and warrants in one go.

### 3. Role and User Management
- **Role Management**: Utilize Discord role IDs to assign permissions for viewing or editing NCIC data. 
  - `permlevel` system: Higher-ranking roles have additional privileges such as the ability to edit or update records.
- **User Management**: Admin users can create, edit, and manage other users within the system to ensure proper access control.

## Setup

1. **Download the Project**:
   - Click the green "Code" button above and select "Download ZIP".
   - Extract the ZIP file to your `htdocs` folder (e.g., `C:/xampp/htdocs`).

2. **Configure the Environment**:
   - Open the `config.php` file.
   - Set the database connection parameters (`host`, `username`, `password`, `database`).
   - Input your Discord bot token and configure role IDs for access control.

3. **Import the Database**:
   - Open your database management tool (e.g., phpMyAdmin).
   - Create a new database (e.g., `ncic_db`).
   - Import the `database.sql` file into the newly created database.

4. **Access the Application**:
   - Open your web browser.
   - Navigate to `http://localhost/stoic-ncic` to access the Stoic-NCIC system.

## Requirements
- **PHP 7.4 or higher**
- **MySQL** database server
- **Discord Bot Token** (for role management)


