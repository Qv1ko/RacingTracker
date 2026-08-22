<div align="center">
  <a href="https://github.com/Qv1ko/RacingTracker">
    <img src="public/logo.svg" alt="RacingTracker" width="120" height="120">
  </a>

  <h1 align="center">RacingTracker</h1>

[![Composer 2.8](https://img.shields.io/badge/Composer_2.8-885630?style=for-the-badge&logo=composer&logoColor=E3E3E3&labelColor=333333)](https://getcomposer.org)
[![Laravel 13](https://img.shields.io/badge/Laravel_13-FF2D20?style=for-the-badge&logo=laravel&logoColor=E3E3E3&labelColor=333333)](https://laravel.com)
[![Node.js 26](https://img.shields.io/badge/Node.js_26-5FA04E?style=for-the-badge&logo=node.js&logoColor=E3E3E3&labelColor=333333)](https://nodejs.org)
[![PHP 8.4](https://img.shields.io/badge/PHP_8.4-777BB4?style=for-the-badge&logo=php&logoColor=E3E3E3&labelColor=333333)](https://www.php.net)
[![pnpm 11](https://img.shields.io/badge/pnpm_11-F9AD00?style=for-the-badge&logo=pnpm&logoColor=E3E3E3&labelColor=333333)](https://pnpm.io)
[![React 19](https://img.shields.io/badge/React_19-61DAFB?style=for-the-badge&logo=react&logoColor=E3E3E3&labelColor=333333)](https://react.dev)
[![SQLite 3.47](https://img.shields.io/badge/SQLite_3.47-003B57?style=for-the-badge&logo=sqlite&logoColor=E3E3E3&labelColor=333333)](https://www.sqlite.org)
[![TailwindCSS 4](https://img.shields.io/badge/TailwindCSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=E3E3E3&labelColor=333333)](https://tailwindcss.com)
[![Vite 8](https://img.shields.io/badge/Vite_8-646CFF?style=for-the-badge&logo=vite&logoColor=E3E3E3&labelColor=333333)](https://vite.dev)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=E3E3E3&labelColor=333333)](https://www.docker.com)

  <p align="center">
    <a href="https://github.com/Qv1ko/RacingTracker/tree/main/documents">🇪🇸 Docs</a> 
    &middot;
    <a href="https://github.com/Qv1ko/RacingTracker/issues/new">Report issue</a>
  </p>

</div>

## ℹ️ About

RacingTracker is a free and open source web application that allows you to manage races in the world of motor racing with an innovative scoring system.

### Why RacingTracker?

In motor racing, the predominant scoring system is based exclusively on the final position of the participants. This traditional approach assigns fixed scores to each position without considering key factors such as relative performance, prior expectations or the particular conditions of each driver or team. As a result, the current system has two main shortcomings:

1. **Lack of recognition of relative performance**: A driver or team with limited resources who achieves a modest result could be performing at an exceptional level based on their capabilities, but the system does not reflect this merit.

2. **Little appreciation for exceeding expectations**: A pilot from a leading team who finishes in a high position simply meets expectations, without receiving additional recognition if his performance exceeds initial projections.

These limitations raise a fundamental question: is a scoring system that only considers final position without assessing context and relative performance really equitable? The answer, from my perspective, is no. I therefore propose the development of a fairer and more dynamic ranking system, capable of more accurately reflecting the true merit of each driver and team.

### 📁 Project structure

```bash
📁 root/
|-- app/                         # Application core
|   |-- Actions/                 # Business actions (e.g. race participation sync)
|   |-- Contracts/               # Interfaces (rating calculation strategies)
|   |-- Events/                  # Domain events
|   |-- Http/
|   |   |-- Controllers          # Route controllers
|   |   |-- Middleware           # HTTP middleware
|   |   \-- Requests             # Form request validation
|   |-- Listeners/               # Event listeners (rating calculations)
|   |-- Models                   # Eloquent models
|   |-- Providers                # Service providers
|   \-- Services                 # Domain services (ranking, stats, presentation)
|
|-- database/                    # Database files
|   |-- factories                # Model factories for seeding
|   |-- migrations               # Database schema migrations
|   |-- seeders                  # Database seeders
|   \-- database.sqlite          # SQLite database
|
|-- documents/                   # Project documentation
|   |-- resources                # Images, diagrams, etc.
|   \-- memoria.docx             # Project document
|
|-- public/                      # Publicly accessible files
|
|-- resources/                   # Frontend resources
|   |-- css                      # CSS styles
|   |-- js/                      # TypeScript frontend code
|   |   |-- components           # Reusable components
|   |   |-- hooks                # Custom React hooks
|   |   |-- layouts              # Layout components
|   |   |-- lib                  # Utility functions
|   |   |-- pages                # Page-level components
|   |   \-- types                # TypeScript types/interfaces
|   \-- views                    # Blade views
|
\-- routes/                      # Route definitions

compose.yaml                     # Docker Compose (Laravel Sail + Node/pnpm)
pint.json                        # PHP code style configuration
phpstan.neon                     # Static analysis configuration (Larastan)
```

## 📊 Points calculation

### Variables

- `β`: performance deviation (μ/6.0)
- `μ`: current driver points (25.0)
- `τ`: dynamic factor of change (μ/300.0)
- `σ`: current driver uncertainty (μ/3.0)
- `A`: expected average position
- `F`: final position in the race
- `P`: number of race participants

### Operations

**Performance deviation:**

$`β=μ/6.0`$

**Dynamic factor of change:**

$`τ=μ/300.0`$

**Combined variance of performance:**

$`C=σ^2+β^2`$

**Updating factor:**

$`K=σ^2/C`$

**Error between expected and actual position:**

$`E=A-F`$

**Updating `μ`:**

$`μ_{new}=μ+KE`$

**Impact of error in `σ`:**

$`I=∣E∣/P`$

**Proposed change in `σ`:**

$`σ_{change}=τ(0.5−I)`$

**`σ` maximum change limit (15%):**

$`M_{change}=σ(0.15)`$

**Application of limits to `σ` change if $`σ_{change}>0`$:**

$`σ_{change}=min⁡(σ_{change}, M_{change})`$

**Application of limits to `σ` change if $`σ_{change}<0`$:**

$`σ_{change}=max⁡(σ_{change}, -M_{change})`$

**`σ` update (with minimum 0.001):**

$`σ_{new}=max⁡(0.001, σ+σ_{change})`$

## 🚀 Deployment locally

### Prerequisites

- [PHP ^8.4](https://www.php.net/downloads.php)
- [Node.js ^26](https://nodejs.org/en/download/)
- [pnpm ^11](https://pnpm.io/installation)
- [Composer ^2.8](https://getcomposer.org/download/)

### Installation

1. Clone the repository:

```bash
git clone https://github.com/Qv1ko/RacingTracker.git
```

2. Change the working directory:

```bash
cd RacingTracker
```

3. Install the dependencies:

```bash
composer install && pnpm install
```

4. Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

5. Generate the application key:

```bash
php artisan key:generate
```

6. Run the migrations:

```bash
php artisan migrate
```

7. Run the seeders:

```bash
php artisan db:seed
```

8. Run the server:

```bash
pnpm run build && php artisan serve
```

9. Open [http://localhost:8000](http://0.0.0.0:8000) in your browser.

## 🐳 Deployment with Docker

### Prerequisites

- [Docker Engine](https://docs.docker.com/engine/install/) with Docker Compose v2

### Usage

1. Start the containers (Laravel PHP 8.5 via Sail + Node/pnpm for assets):

```bash
./vendor/bin/sail up -d
```

2. Install the PHP dependencies inside the container:

```bash
./vendor/bin/sail composer install
```

3. Copy the `.env.example` file to `.env`, then generate the application key and run the migrations:

```bash
cp .env.example .env
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --graceful
```

4. The Node container automatically installs the frontend dependencies with pnpm and starts the Vite dev server on port 5173. To build the assets for production instead:

```bash
./vendor/bin/sail exec node pnpm run build
```

5. Open [http://localhost](http://localhost) in your browser.

> Useful commands: `./vendor/bin/sail down` (stop), `./vendor/bin/sail pest` (run tests), `./vendor/bin/sail composer lint` / `analyse` (code quality).

## 🧹 Code quality

The project enforces code style and static analysis through Composer scripts:

```bash
# Check code style (Pint + oxfmt) without fixing
composer lint

# Fix code style automatically (Pint + oxfmt)
composer lint:fix

# Run static analysis (Larastan, level 5)
composer analyse
```

Additional frontend-only checks are available via pnpm:

```bash
pnpm run types   # TypeScript type checking
pnpm run lint    # oxlint
pnpm run fmt     # oxfmt formatter
```

## 🏆 Rating algorithms

The application calculates its own driver and team ratings every time a race result is created or updated. The active algorithm is selected in your `.env` file:

```bash
RANKING_ALGORITHM=trueskill   # available: trueskill | classic | position
```

| Algorithm   | Description |
| ----------- | ----------- |
| `trueskill` *(default)* | TrueSkill-inspired rating system (see [Points calculation](#-points-calculation)). Each driver has points `μ` and uncertainty `σ` that evolve with every result. |
| `classic` | Fixed F1 points table by final position (25, 18, 15...). Configurable in `config/ranking.php` under `classic_points`. |
| `position` | Rating based purely on the final position of each participant. |

The mapping between algorithm names and classes lives in `config/ranking.php` under `algorithms`.

### Changing the algorithm

1. Set `RANKING_ALGORITHM` in your `.env` to one of the available options.
2. Recalculate all stored ratings so existing races use the new system:

```bash
php artisan ranking:recalculate
```

You can also recalculate a single season or only races after a given date:

```bash
php artisan ranking:recalculate --season=2025
php artisan ranking:recalculate --from=2025-01-01
```

### Adding a custom algorithm

1. Create a class implementing the `App\Contracts\RatingCalculation` contract:

```php
<?php

namespace App\Listeners\Calculations;

use App\Contracts\RatingCalculation;
use App\Events\RaceResultCalculated;

class MySystemCalculation implements RatingCalculation
{
    public function handle(RaceResultCalculated $event): void
    {
        foreach ($event->participations as $participation) {
            // $participation->position, $participation->race->date, etc.
            $participation->points = 0; // your formula here
            $participation->uncertainty = 0;
            $participation->save();
        }
    }
}
```

2. Register it in `config/ranking.php`:

```php
'algorithms' => [
    // ...
    'my-system' => MySystemCalculation::class,
],
```

3. Select it with `RANKING_ALGORITHM=my-system` in `.env` and recalculate as explained above.

> Note: ratings are reset at the start of every season, so all drivers and teams begin each season from the same neutral value and the season champion is simply the one with the most points at the end of the last race.

## 🔄 Automation

The scheduler keeps data and ratings up to date automatically (see `routes/console.php`):

| Schedule | Command | Description |
| -------- | ------- | ----------- |
| Daily at 04:00 | `f1:sync` | Imports missing seasons, races and results from the [Jolpica API](https://api.jolpi.ca) (current and previous season by default). Existing data is never overwritten, so manual edits always win. |
| Weekly on Mondays at 05:00 | `ranking:recalculate` | Full ratings recalculation as a consistency safety net after each Grand Prix weekend. |

In production, make sure the Laravel scheduler runs every minute via cron:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

For local development you can run it with:

```bash
php artisan schedule:work
```

To import the full history from scratch at any time:

```bash
php artisan f1:sync --from=1950
```

## ☁️ Deployment on AWS

### Prerequisites

- AWS account
- Fork the RacingTracker repository

### Installation

#### Create the AWS infrastructure

1. Create a new VPC in AWS.

2. Create a new EC2 instance in AWS:

    - Select Ubuntu OS
    - Create a new key pair
    - Select the VPC you created in the previous step in the network settings
    - Select the public subnet in the network settings
    - Enable the auto-assign public IP option in the network settings
    - Create two new security groups rules:
        - Type: HTTP (80), Source type: Anywhere
        - Type: HTTPS (443), Source type: Anywhere

3. Edit key pair permissions in your local machine:

```bash
sudo chmod 0600 "path/to/your/key.pem"
```

#### Prepare Ubuntu

1. Connect with SSH to the EC2 instance:

```bash
ssh -i "path/to/your/key.pem" ubuntu@ec2-public-ip-address`
```

2. Update the system:

```bash
sudo apt update && sudo apt upgrade -y
```

3. Reboot the instance in AWS.

4. Install the necessary packages:

```bash
sudo apt install nginx -y && sudo add-apt-repository ppa:ondrej/php -y && sudo apt install -y php8.4-fpm php8.4-curl php8.4-xml php8.4-mbstring php8.4-zip php8.4-mysql php8.4-sqlite3 php8.4-redis zip unzip && sudo curl -sS https://getcomposer.org/installer | php && sudo mv ~/composer.phar /usr/local/bin/composer && curl -fsSL https://deb.nodesource.com/setup_26.x | sudo -E bash - && sudo apt install nodejs -y && sudo npm install -g pnpm && composer --version && node -v && pnpm -v
```

5. Create a new user to make deployment more secure:

```bash
sudo adduser deploy-user
```

6. Access the new user home directory:

```bash
sudo su deploy-user && cd ~
```

7. Generate GitHub ssh keys for the new user:

```bash
ssh-keygen -f /home/deploy-user/.ssh/github_rsa -t rsa
```

8. Create a new GitHub config file:

```bash
nano /home/deploy-user/.ssh/config
```

```bash
Host github.com
  IdentityFile ~/.ssh/github_rsa
  IdentitiesOnly yes
```

9. Change ssh directory and files permissions:

```bash
chmod 700 ~/.ssh && chmod 600 ~/.ssh/*
```

#### Application deployment

1. Add a new deploy key in the forked GitHub repository where its value will be the key `github_rsa.pub`:

```bash
cat ~/.ssh/github_rsa.pub
```

2. Clone the forked repository:

```bash
git clone git@github.com:your-username/RacingTracker.git code && cd code
```

3. Install the dependencies:

```bash
composer install && pnpm install
```

4. Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

5. Generate the application key:

```bash
php artisan key:generate
```

6. Run the migrations:

```bash
php artisan migrate
```

7. Run the seeders:

```bash
php artisan db:seed
```

8. Build the frontend:

```bash
pnpm run build
```

9. Remove default file in sites-enabled:

```bash
exit
```

```bash
sudo rm /etc/nginx/sites-enabled/default
```

10. Create a config file in sites-available:

```bash
sudo nano /etc/nginx/sites-available/racingtracker.conf
```

```bash
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /home/deploy-user/code/public; # Change deploy-user to your username
    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

11. Create a symlink to the config file in sites-enabled:

```bash
sudo ln -s /etc/nginx/sites-available/racingtracker.conf /etc/nginx/sites-enabled/
```

12. Test if the configuration is correct:

```bash
sudo nginx -t
```

13. Reload the nginx service:

```bash
sudo service nginx reload
```

14. Add the nginx user in a new group with deploy-user:

```bash
sudo usermod -aG deploy-user www-data
```

15. Modify php-fpm pool configuration:

```bash
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
```

```bash
; pool name ('www' here)
[deploy-user]

...

user = deploy-user
group = deploy-user

...

listen.owner = deploy-user
listen.group = deploy-user
```

16. Restart the php-fpm service:

```bash
sudo service php8.4-fpm restart
```

17. You can now access the application at [http://ec2-public-ip-address](http://ec2-public-ip-address).

#### Installing a self-signed SSL/TLS certificate

1. Install the necessary dependencies:

```bash
sudo apt install openssl -y
```

2. Create a new directory for the SSL/TLS certificate:

```bash
sudo su deploy-user
```

```bash
mkdir ~/.ssl && cd ~/.ssl
```

3. Create a config file in a new directory:

```bash
nano san.cnf
```

```bash
[req]
default_bits       = 2048
prompt             = no
default_md         = sha256
req_extensions     = req_ext
distinguished_name = dn

[dn]
C  = # Country
ST = # State
L  = # Locality
O  = # Organization
OU = # Organizational Unit
CN = XX.XX.XX.XX # EC2 instance public IP address

[req_ext]
subjectAltName = @alt_names

[alt_names]
IP.1 = XX.XX.XX.XX # EC2 instance public IP address
```

4. Generate a RSA key pair:

```bash
openssl genrsa -out server.key 2048
```

5. Generate the certificate signing request (CSR):

```bash
openssl req -x509 -nodes -newkey rsa:2048 \
  -keyout server.key \
  -out server.crt \
  -days 365 \
  -subj "/CN=XX.XX.XX.XX"
```

6. Edit nginx configuration file:

```bash
exit
```

```bash
sudo nano /etc/nginx/sites-available/racingtracker.conf
```

```bash
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    listen 443 ssl;
    server_name XX.XX.XX.XX; # EC2 instance public IP address
    root /home/deploy-user/code/public; # Change deploy-user to your username
    index index.html index.htm index.php;

    ssl_certificate     /home/deploy-user/.ssl/server.crt; # Change deploy-user to your username
    ssl_certificate_key /home/deploy-user/.ssl/server.key; # Change deploy-user to your username

    # Redirect HTTP to HTTPS
    if ($scheme != "https") {
      return 301 https://$server_name$request_uri;
    }

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

7. Check if the configuration is correct and reload the nginx service:

```bash
sudo nginx -t && sudo systemctl reload nginx
```

8. You can now access the application at [https://ec2-public-ip-address](https://ec2-public-ip-address).

## 📄 License

Distributed under the MIT License. See [LICENSE](https://github.com/Qv1ko/RacingTracker/blob/main/LICENSE) for more information.

## 📚 References

- [Mr V's Garage inspiring video](https://www.youtube.com/live/U16a8tdrbII)
- [TrueSkill System](https://en.wikipedia.org/wiki/TrueSkill)
- [Pitwall App](https://pitwall.app)

