1. clone project (once per computer)

git clone https://github.com/Z3R0HUB/TanentClinicSystem.git

clone = download project first time
pull = update project

2. Update Project

   git pull origin develop

3. Upload to github

        git add .
        git commit -m "Fix login bug"
        git push origin develop

==============================================
##Provice Import

## How to use docker

Run setup database

```apacheconf
docker compose -f docker-compose.local.yml up -d
```

Check container status

```apacheconf
docker ps
```

## PostgreSQL UI like phpMyAdmin

Access:

```apacheconf
http://localhost:5050
email: admin@local.com
password: admin
```
