<?php include 'includes/header.php'; ?>


    <!-- Nền động (Animation) -->
    <div class="animated-background">
        <canvas id="networkCanvas"></canvas>
    </div>

    <!-- Dịch vụ Xe Tuyến Liên Tỉnh -->
    <div class="container mx-auto max-w-6xl p-8 mt-12 z-10 relative">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8 flex flex-col md:flex-row items-center">
            <div class="w-full md:w-1/2">
                <img src="https://www.goads.vn/image/goads-adroad.jpg" 
                     alt="Dịch vụ xe tuyến liên tỉnh" 
                     class="w-full h-auto object-cover rounded-2xl">
            </div>
            <div class="w-full md:w-1/2 p-8 text-center md:text-left">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">DỊCH VỤ XE TUYẾN LIÊN TỈNH</h3>
                <p class="text-gray-600 leading-relaxed">
                    Đây là một giải pháp quảng cáo mới mẻ, mang đến một hình thức quảng cáo độc đáo trên các tuyến xe liên tỉnh. Xe khách du lịch chạy quảng cáo di chuyển thường xuyên giữa các tỉnh thành, giúp nhãn hàng tiếp cận được số lượng lớn khách hàng từ nhiều tỉnh thành.
                </p>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('networkCanvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        const particleCount = 100;
        const maxDistance = 150;

        const resizeCanvas = () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        };
        
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        class Particle {
            constructor(x, y) {
                this.x = x;
                this.y = y;
                this.size = Math.random() * 2 + 1;
                this.vx = (Math.random() - 0.5) * 1;
                this.vy = (Math.random() - 0.5) * 1;
            }

            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
                ctx.fill();
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                if (this.x < 0 || this.x > canvas.width) this.vx *= -1;
                if (this.y < 0 || this.y > canvas.height) this.vy *= -1;
            }
        }

        const createParticles = () => {
            for (let i = 0; i < particleCount; i++) {
                const x = Math.random() * canvas.width;
                const y = Math.random() * canvas.height;
                particles.push(new Particle(x, y));
            }
        };

        const connectParticles = () => {
            for (let a = 0; a < particles.length; a++) {
                for (let b = a; b < particles.length; b++) {
                    const dx = particles[a].x - particles[b].x;
                    const dy = particles[a].y - particles[b].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < maxDistance) {
                        ctx.beginPath();
                        ctx.strokeStyle = `rgba(0, 0, 0, ${1 - distance / maxDistance})`;
                        ctx.lineWidth = 0.5;
                        ctx.moveTo(particles[a].x, particles[a].y);
                        ctx.lineTo(particles[b].x, particles[b].y);
                        ctx.stroke();
                    }
                }
            }
        };

        const animate = () => {
            requestAnimationFrame(animate);
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();
            }
            connectParticles();
        };

        createParticles();
        animate();

    </script>


<?php include 'includes/footer.php'; ?>
