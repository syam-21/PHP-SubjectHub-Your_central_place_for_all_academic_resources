<?php
$page_title = "Welcome to SubjectHub";
require_once 'config/database.php';
include 'templates/partials/header.php';
?>

<style>
    body, html {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
        font-family: 'Poppins', sans-serif;
    }

    #canvas-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .welcome-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
        text-align: center;
        color: white;
        position: relative;
        z-index: 2;
    }

    .welcome-title {
        font-size: 5rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
        animation: slideInFromTop 1.5s ease-out;
    }

    .welcome-subtitle {
        font-size: 1.8rem;
        margin-top: 1.5rem;
        letter-spacing: 1px;
        text-shadow: 1px 1px 6px rgba(0, 0, 0, 0.5);
        animation: slideInFromBottom 1.5s ease-out;
    }

    .btn-goto {
        margin-top: 3rem;
        padding: 1rem 3rem;
        font-size: 1.2rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 2px;
        border-radius: 50px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 2px solid white;
        text-decoration: none;
        transition: all 0.4s ease;
        animation: fadeIn 2s ease-out 1s;
        animation-fill-mode: both;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
    }
    
    .btn-goto:hover {
        background: white;
        color: #333;
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    @keyframes slideInFromTop {
        from { transform: translateY(-100px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes slideInFromBottom {
        from { transform: translateY(100px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<canvas id="canvas-background"></canvas>

<div class="welcome-container">
    <h1 class="welcome-title">Welcome to SubjectHub</h1>
    <p class="welcome-subtitle">Your central place for all academic resources.</p>
    <a href="<?php echo BASE_URL; ?>/templates/login.php" class="btn-goto">Get Started</a>
</div>

<script>
    const canvas = document.getElementById('canvas-background');
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const colors = ["#6DD5FA", "#2980B9", "#FFFFFF"];
    const circles = [];

    for (let i = 0; i < 100; i++) {
        circles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            radius: Math.random() * 3 + 1,
            vx: Math.random() * 0.5 - 0.25,
            vy: Math.random() * 0.5 - 0.25,
            color: colors[Math.floor(Math.random() * colors.length)]
        });
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#001f3f'; // Dark blue background
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        for (let i = 0; i < circles.length; i++) {
            const circle = circles[i];
            ctx.beginPath();
            ctx.arc(circle.x, circle.y, circle.radius, 0, Math.PI * 2);
            ctx.fillStyle = circle.color;
            ctx.fill();

            circle.x += circle.vx;
            circle.y += circle.vy;

            if (circle.x < 0 || circle.x > canvas.width) {
                circle.vx = -circle.vx;
            }
            if (circle.y < 0 || circle.y > canvas.height) {
                circle.vy = -circle.vy;
            }
        }

        requestAnimationFrame(draw);
    }

    draw();

    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
</script>


