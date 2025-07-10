# securenote
This is a website I built from scratch including server configuration and security without using any frameworks. I used Apache2, MySQL, PHPMyAdmin, and HTTPS. For security, I implemented Fail2Ban, SQL injection and XSS protection, and a custom login panel with user access control.
The purpose of this website is to provide personalized notes for students. Each student must search for their name, and if there is a note associated with them, it will appear encrypted. To access the full content of the note, the student must enter their university account password, which serves as the decryption key.

On the admin side, when creating a note, they do not need to manually set an encryption key. Once a student is selected, the system will automatically use the student’s university password as the encryption key for that note.
