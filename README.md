# security-bundle
Provides user management for Symfony Project.

## Configuration

### Security
Minimum configuration in `config/security.yaml` file for your securited firewall :
```
main:
    pattern:   ^/api/login

    code202-login:
        check_path: /api/login
```
You also can disable or configure an authentication way like this :
```
main:
    pattern:   ^/api/login

    code202-login:
        check_path: /api/login
        success_handler: lexik_jwt_authentication.handler.authentication_success
        failure_handler: lexik_jwt_authentication.handler.authentication_failure
        username_password_json:
            check_path: /username
            username_parameter: username
            password_parameter: pass
            remember_me_parameter: remember
        username_password_form:
            check_path: /username
        token_by_email_json:
            check_path: /email
        token_by_email_form: false
```

### Routing
Add to folowing route in your `routing.yaml` :
```
security:
    resource: '@Code202SecurityBundle/Resources/config/routing.yml'
    name_prefix: api.security
    prefix: api/security
```
Prefix has to correspond to your firewall pattern !

### Uuid
You have to choose how to generate and validate UUID.
```
# config/code202_security.yaml
code202_security:
    uuid:
        generator: 'your service name here'
        validator: 'your service name here'
```
Your service name have to respectivly implements Code202\Security\Uuid\UuidGeneratorInterface and Code202\Security\Uuid\UuidValidatorInterface

You also can use these values `ramsey/uuid` or `symfony/polyfill-uuid` (default) if you use the corresponding packages.

### Session TTL
You can provide differents values of Time To Live for the differents authentications ways, the default value is 3600 seconds.
```
# config/code202_security.yaml
code202_security:
    sessionTTL:
        username_password: 7200
        token_by_email: 1800
```

### Token By Email
You can configure the 'token_by_email' authentication behavior :
```
# config/code202_security.yaml
code202_security:
    token_by_email:
        refresher:
            token_generator: 'your_generator_service_name'
                #The service have to implement Code202Security\Service\Common\TokenGeneratorInterface
                # Default value : 'number_base' to use our generator
            minimal_refresh_interval: 'time_interval' # Default '1 minute'
            lifetime_interval: 'time_interval' #Default '5minutes'
```

### Token Generator
If you choose to use the default token generaotr, you can configure it with :
```
# config/code202_security.yaml
code202_security:
    token_generator:
        number_base:
            size: 6 # The size of the generated token
```

### Roles Strategies
The roles strategies explains which roles can be grant and revoke with which conditions.
For example :
```
# config/code202_security.yaml
code202_security:
    role_strategies:
        -   roles:
                - 'ROLE_1'
                - 'ROLE_5'
            to_grant: 'is_granted("ROLE_SUPER_ADMIN")'
        -   roles:
                - 'ROLE_1'
            to_grant: 'is_granted("ROLE_ADMIN")'
            to_revoke: 'is_granted("ROLE_SUPER_ADMIN")'
        -   roles:
                - 'ROLE_2'
            to_grant: 'is_granted("ROLE_1")'
```
By default, if `to_revoke` option in note define, the `to_grant` option is apply for revoke conditions.

## Bridges

### Nelmio/ApiDocBundle

This bundle already use OpenApi attributes. The best way to use it is to import our configuration in your `nelmio-apièdoc.yaml` file
```
imports:
    - { resource: '@Code202SecurityBundle/Resources/config/nelmio_api_doc.yaml' }

nelmio_api_doc:
    documentation:
        info:
            title: OB2B BO
            description: Test application
            version: 1.0.0

    areas: # to filter documented areas
        path_patterns:
            - ^/api(?!/(doc|security)) # Accepts routes under /api except /api/doc
```

In cases where you change key, password or remember_me parameters on authenticators, you can override this configuration like this :
```
    documentation:
        info:
            title: OB2B BO
            description: Test application
            version: 1.0.0

        components:
            schemas:
                LoginUsernameRequest:
                    properties:
                        login:
                            type: string
                        pass:
                            type: string
                        rememberMe:
                            type: boolean

    areas: # to filter documented areas
        path_patterns:
            - ^/api(?!/(doc|security)) # Accepts routes under /api except /api/doc
```

You can add these route in your configuration to show login routes in your API documentation:
```
security-login:
    resource: '@Code202SecurityBundle/Resources/config/routing-loging.yml'
    name_prefix: api.security
```

## Dev notes

### Use docker container
Export .home-developer path to $DEV
```
    export DEV ~/srv
```

Launch container
```
    make console
```

#### To clean code
```
    tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src
```
You can use `--dry` option
