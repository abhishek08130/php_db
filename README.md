**DATABASE BACKUP WEBSITE**
The DB BACKUP PHP script serves as a tool for managing MySQL databases by allowing users to list databases, generate backups, and view existing backups. Here's a detailed description of its functionality:

**1. Database Connection Setup:**

The script sets up parameters for connecting to a MySQL database, including the hostname, username, and password. This allows it to interact with the MySQL server.

**2. Listing Databases:**
Upon establishing a connection to the MySQL server, the script retrieves a list of databases.
It filters out system databases such as 'information_schema', 'mysql', and 'performance_schema'.
The filtered list of databases is displayed in an intuitive format, showing each database's name and size in megabytes.

**3. Listing Backup Files:**
The script fetches a list of existing backup files stored in a designated folder on the server.
For each backup file, it provides a downloadable link along with details such as filename, size, and modification time.

**4. Backup Generation:**
Users can initiate a backup of a selected database by clicking on its name.
Upon selection, a JavaScript function triggers a request to the server, specifying the chosen database for backup.
The server-side script responds by executing the mysqldump command to create a backup file of the selected database.
The newly generated backup file is saved in the predefined backup folder with a timestamp appended to its filename.
