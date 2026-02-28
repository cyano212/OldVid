  ______  __       __ __     __ __       __ 
 /      \|  \     |  \  \   |  \  \     |  \
|  ▓▓▓▓▓▓\ ▓▓ ____| ▓▓ ▓▓   | ▓▓\▓▓ ____| ▓▓
| ▓▓  | ▓▓ ▓▓/      ▓▓ ▓▓   | ▓▓  \/      ▓▓
| ▓▓  | ▓▓ ▓▓  ▓▓▓▓▓▓▓\▓▓\ /  ▓▓ ▓▓  ▓▓▓▓▓▓▓
| ▓▓  | ▓▓ ▓▓ ▓▓  | ▓▓ \▓▓\  ▓▓| ▓▓ ▓▓  | ▓▓
| ▓▓__/ ▓▓ ▓▓ ▓▓__| ▓▓  \▓▓ ▓▓ | ▓▓ ▓▓__| ▓▓
 \▓▓    ▓▓ ▓▓\▓▓    ▓▓   \▓▓▓  | ▓▓\▓▓    ▓▓
  \▓▓▓▓▓▓ \▓▓ \▓▓▓▓▓▓▓    \▓    \▓▓ \▓▓▓▓▓▓▓
                                            
                                            
                                            
Hello i'm open-sourceing my revival with the 1° layout i made

this is the old RetroShow version but modified 

free to use and distribute 


/* =========================
   Issues Answers
========================= */

I: i'm getting "Warning: move_uploaded_file(uploads/tempfile.mp4): Failed to open stream: Permission denied in /var/www/html/upload.php on line 41"

S:use chmod 777 to /uploads folder

I: i'm getting "column not found time on videos(idfk the original error)"

S:go to your sqlite db using "sqlite3 /your/sqlite/db/path/" and execute
"ALTER TABLE videos ADD COLUMN time INTEGER;"

I:my site get raided

S:backup the files and the db if you can

I:after the backup the sqlite db is showing "you are trying write in a read-only db(idfk the original error again)"

S: put the chmod 777 permissions on the sqlite file

btw

Enjoy this source-code