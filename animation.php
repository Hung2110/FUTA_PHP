<?php include 'header.php'; ?>

    <canvas id="canvas"></canvas>

    <div class="blue-shape"></div>
    <div class="blue-shape"></div>
    <div class="blue-shape"></div>
    <div class="blue-shape"></div>


    <script>
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');

        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        const particles = [];
        const maxParticles = 100; // Số lượng chấm

        // Hàm tạo hạt
        function Particle(x, y, radius, color) {
            this.x = x;
            this.y = y;
            this.radius = radius;
            this.color = color;
            this.velocity = {
                x: (Math.random() - 0.5) * 0.5, // Tốc độ di chuyển ngẫu nhiên
                y: (Math.random() - 0.5) * 0.5
            };

            this.draw = function() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2, false);
                ctx.fillStyle = this.color;
                ctx.fill();
            };

            this.update = function() {
                this.x += this.velocity.x;
                this.y += this.velocity.y;

                // Giữ hạt trong màn hình
                if (this.x < 0 || this.x > width) this.velocity.x = -this.velocity.x;
                if (this.y < 0 || this.y > height) this.velocity.y = -this.velocity.y;

                this.draw();
            };
        }

        // Khởi tạo hạt
        function init() {
            particles.length = 0; // Xóa các hạt cũ
            for (let i = 0; i < maxParticles; i++) {
                const radius = Math.random() * 2 + 1; // Bán kính từ 1 đến 3
                const x = Math.random() * (width - radius * 2) + radius;
                const y = Math.random() * (height - radius * 2) + radius;
                const color = 'rgba(190, 190, 190, 0.5)'; // Màu xám nhạt trong suốt
                particles.push(new Particle(x, y, radius, color));
            }
        }

        // Nối các hạt bằng đường
        function connectParticles() {
            for (let i = 0; i < particles.length; i++) {
                for (let j = i; j < particles.length; j++) {
                    const p1 = particles[i];
                    const p2 = particles[j];
                    const distance = Math.sqrt(
                        (p1.x - p2.x) * (p1.x - p2.x) +
                        (p1.y - p2.y) * (p1.y - p2.y)
                    );

                    const maxDistance = 120; // Khoảng cách tối đa để nối đường
                    if (distance < maxDistance) {
                        ctx.beginPath();
                        ctx.moveTo(p1.x, p1.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.strokeStyle = `rgba(190, 190, 190, ${1 - (distance / maxDistance)})`; // Độ mờ của đường
                        ctx.lineWidth = 0.5;
                        ctx.stroke();
                    }
                }
            }
        }

        // Vòng lặp hoạt hình
        function animate() {
            requestAnimationFrame(animate);
            ctx.clearRect(0, 0, width, height); // Xóa canvas
            ctx.fillStyle = '#f7f7f7'; // Đặt màu nền cho canvas
            ctx.fillRect(0, 0, width, height);

            connectParticles();

            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
            }
        }

        // Xử lý thay đổi kích thước cửa sổ
        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
            init(); // Khởi tạo lại hạt khi kích thước thay đổi
        });

        init();
        animate();
    </script>

<?php include 'footer.php'; ?>
