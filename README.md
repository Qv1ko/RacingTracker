<div align="center">
  <a href="https://github.com/Qv1ko/RacingTracker">
    <img src="public/logo.svg" alt="RacingTracker" width="120" height="120">
  </a>

  <h1 align="center">RacingTracker</h1>

![Composer 2.8](https://img.shields.io/badge/Composer_2.8-885630?style=for-the-badge&logo=composer&logoColor=E3E3E3&labelColor=333333)
![Laravel 12](https://img.shields.io/badge/Laravel_12-FF2D20?style=for-the-badge&logo=laravel&logoColor=E3E3E3&labelColor=333333)
![Node.js 22.14](https://img.shields.io/badge/Node.js_22.14-5FA04E?style=for-the-badge&logo=node.js&logoColor=E3E3E3&labelColor=333333)
![PHP 8.2](https://img.shields.io/badge/PHP_8.2-777BB4?style=for-the-badge&logo=php&logoColor=E3E3E3&labelColor=333333)
![SQLite 3.47](https://img.shields.io/badge/SQLite_3.47-003B57?style=for-the-badge&logo=sqlite&logoColor=E3E3E3&labelColor=333333)
![TailwindCSS 3.3](https://img.shields.io/badge/TailwindCSS_3.3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=E3E3E3&labelColor=333333)
![Vite 6.2](https://img.shields.io/badge/Vite_6.2-646CFF?style=for-the-badge&logo=vite&logoColor=E3E3E3&labelColor=333333)

  <p align="center">
    <a href="#">Demo</a>
    &middot;
    <a href="https://github.com/Qv1ko/RacingTracker/tree/main/documents">🇪🇸 Docs</a> 
    &middot;
    <a href="https://github.com/Qv1ko/RacingTracker/issues">Report issue</a>
  </p>

</div>

## About

RacingTracker is a free and open source web application that allows you to manage races in the world of motor racing with an innovative scoring system.

### Why RacingTracker?

In motor racing, the predominant scoring system is based exclusively on the final position of the participants. This traditional approach assigns fixed scores to each position without considering key factors such as relative performance, prior expectations or the particular conditions of each driver or team. As a result, the current system has two main shortcomings:

1. **Lack of recognition of relative performance**: A driver or team with limited resources who achieves a modest result could be performing at an exceptional level based on their capabilities, but the system does not reflect this merit.

2. **Little appreciation for exceeding expectations**: A pilot from a leading team who finishes in a high position simply meets expectations, without receiving additional recognition if his performance exceeds initial projections.

These limitations raise a fundamental question: is a scoring system that only considers final position without assessing context and relative performance really equitable? The answer, from my perspective, is no. I therefore propose the development of a fairer and more dynamic ranking system, capable of more accurately reflecting the true merit of each driver and team.

### Points calculation

#### Variables:

- `μ`: current driver points
- `σ`: current driver uncertainty
- `F`: final position in the race
- `A`: expected average position
- `P`: number of race participants
- `β`: performance deviation
- `τ`: dynamic factor of change

Operations:

Performance deviation:

$`β=μ/6.0`$

Dynamic factor of change:

$`τ=μ/300.0`$

Combined variance of performance:

$`C=σ^2+β^2`$

Updating factor:

$`K=σ^2/C`$

Error between expected and actual position:

$`E=A-F`$

Updating `μ`:

$`μ_{new}=μ+KE`$

Impact of error in `σ`:

$`I=∣E∣/P`$

Proposed change in `σ`:

$`σ_{change}=τ(0.5−I)`$

`σ` maximum change limit (15%):

$`M_{change}=σ(0.15)`$

Application of limits to `σ` change if $`σ_{change}>0`$:

$`σ_{change}=min⁡(σ_{change}, M_{change})`$

Application of limits to `σ` change if $`σ_{change}<0`$:

$`σ_{change}=max⁡(σ_{change}, -M_{change})`$

`σ` update (with minimum 0.001):

$`σ_{new}=max⁡(0.001, σ+σ_{change})`$

## Deployment locally

### Prerequisites

- [PHP ^8.2](https://www.php.net/downloads.php)
- [Node.js ^22.14](https://nodejs.org/en/download/)
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
composer install && npm install
```

4. Copy the `.env.example` file to `.env` and update the values:

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

8. Run the development server:

```bash
npm run build && php artisan serve
```

9. Open [http://localhost:8000](http://0.0.0.0:8000) in your browser.

## Deploying RacingTracker on AWS

### Prerequisites

- AWS account

### Installation

...

## Usage

...

## License

Distributed under the MIT License. See [LICENSE](https://github.com/Qv1ko/RacingTracker/blob/main/LICENSE) for more information.

## Acknowledgments

...
