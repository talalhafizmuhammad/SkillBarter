SkillBarter - students trade skills for skills (no money)

SETUP (Ubuntu)
1. sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql
2. sudo cp -r skillbarter /var/www/html/
   sudo chown -R www-data:www-data /var/www/html/skillbarter
3. sudo mysql < /var/www/html/skillbarter/database.sql
   (re-running this resets all data)
4. Create a DB user and set it in config/db.php:
   CREATE USER 'sb'@'localhost' IDENTIFIED BY 'sb12345';
   GRANT ALL ON skillbarter.* TO 'sb'@'localhost';
5. Open http://localhost/skillbarter/
6. Make an admin: UPDATE skillbarter.users SET role='admin' WHERE email='you@example.com';

FLOW
register -> login -> add skills (teach / want) -> browse or matches -> propose swap
-> owner accepts -> chat -> both mark done -> swap completed -> reviews
