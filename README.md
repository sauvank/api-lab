api-lab
=======

A Symfony project created on July 7, 2017, 9:29 am.


## Sample Api symfony 3 with JWT

### Bundle
    * FOSRestBundle
    * NelmioCorsBundle
    * JMSSerializerBundle
    * LexikJWTAuthenticationBundle
    * FOSUserBundle
    
    
### How to run the project ?

 * > cd api-lab
 * > mkdir -p var/jwt
 * > openssl genrsa -out var/jwt/private.pem -aes256 4096 # /!\ The password will be required when composer install
 * > openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
 * > composer install
 * create user with FOSUserBundle :
   > $ php app/console fos:user:create testuser test@example.com p@ssword
 * > curl -X POST http://localhost:8000/api/login_check -d username=testuser -d password=p@ssword
 * api return token object 

