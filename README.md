### Overview
Secure way to share private information with other people. Has ability to set password and change expiration time.  
Before saving data on server, it's encrypted on client side with *[Advanced Encryption Standard (AES)](https://en.wikipedia.org/wiki/Advanced_Encryption_Standard)* Cipher Algorithm.  

### Installation
1. Make **/tmp** folder writeable
2. Skip next steps if project is located in *root* directory
3. Change *RewriteBase* in **.htaccess** from **/** to **/path/to/project/**
4. Change *SITE_FULL_URL* constant in **config.inc.php**

### Example
[http://share.examples.vnat.co/](http://share.examples.vnat.co/)

### Screenshots
![Enter text, password and choose expiration time](screenshots/1.png?raw=true)  
![Copy and share link](screenshots/2.png?raw=true)  
![Open link in browser and enter password](screenshots/3.png?raw=true)  
![Optional: click delete button](screenshots/4.png?raw=true)  