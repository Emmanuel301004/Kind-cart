<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful</title>
    <style>
        /* General Page Styling */
        body {
            font-family: 'Poppins', 'Arial', sans-serif;
            text-align: center;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: #333;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 0;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated Background Bubbles */
        .bubble {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            pointer-events: none;
            animation: float 4s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-20px) translateX(10px); }
        }
        
        /* Card Styling */
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 450px;
            width: 90%;
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            z-index: 10;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }
        
        h2 {
            font-size: 28px;
            margin: 15px 0;
            color: #333;
            font-weight: 600;
        }
        
        p {
            font-size: 18px;
            margin-bottom: 30px;
            color: #666;
            line-height: 1.5;
        }
        
        .buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        
        .btn-primary {
            background: #6e8efb;
            color: white;
            box-shadow: 0 4px 10px rgba(110, 142, 251, 0.3);
        }
        
        .btn-primary:hover {
            background: #5d7cf7;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(110, 142, 251, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        
        .order-timer {
            margin-top: 25px;
            font-size: 14px;
            color: #888;
        }
        
        /* Confetti Animation */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0;
        }
        
        /* Animations */
        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.8); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        @keyframes confettiFall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Background Bubbles -->
    <div id="bubbles-container"></div>
    
    <div class="container">
        <div class="success-icon">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
            </svg>
        </div>
        <h2>Order Placed Successfully!</h2>
        <p>Thank you for your purchase! Your order has been confirmed and is being processed. You'll receive a confirmation email shortly.</p>
        
        <div class="buttons">
            <a href="buy_books.php" class="btn btn-primary">Continue Shopping</a>
            <a href="order_history.php" class="btn btn-secondary">View Orders</a>
        </div>
        
        <div class="order-timer">
            Redirecting to order history in <span id="countdown">3</span> seconds...
        </div>
    </div>
    
    <script>
        // Create bubbles
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('bubbles-container');
            
            for (let i = 0; i < 15; i++) {
                createBubble(container);
            }
            
            // Create confetti burst
            createConfetti();
        });
        
        function createBubble(container) {
            const bubble = document.createElement('div');
            bubble.classList.add('bubble');
            
            // Random size between 20px and 100px
            const size = Math.random() * 80 + 20;
            bubble.style.width = `${size}px`;
            bubble.style.height = `${size}px`;
            
            // Random position
            bubble.style.left = `${Math.random() * 100}%`;
            bubble.style.top = `${Math.random() * 100}%`;
            
            // Random delay
            bubble.style.animationDelay = `${Math.random() * 4}s`;
            
            container.appendChild(bubble);
        }
        
        function createConfetti() {
            const colors = ['#ff598f', '#e542fe', '#3857ff', '#35ffd0', '#ffeb3b'];
            const container = document.body;
            
            for (let i = 0; i < 100; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('confetti');
                
                // Random color
                const colorIndex = Math.floor(Math.random() * colors.length);
                confetti.style.backgroundColor = colors[colorIndex];
                
                // Random position
                confetti.style.left = `${Math.random() * 100}%`;
                
                // Random size
                const size = Math.random() * 10 + 5;
                confetti.style.width = `${size}px`;
                confetti.style.height = `${size}px`;
                
                // Random rotation
                confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
                
                // Animation
                confetti.style.animation = `confettiFall ${Math.random() * 3 + 2}s linear forwards`;
                confetti.style.animationDelay = `${Math.random() * 2}s`;
                
                container.appendChild(confetti);
                
                // Remove after animation
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
        }
        
        // Countdown timer
        let seconds = 3;
        const countdown = document.getElementById('countdown');
        const interval = setInterval(function() {
            seconds--;
            countdown.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = "order_history.php";
            }
        }, 1000);
    </script>
</body>
</html>