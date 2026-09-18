document.addEventListener('DOMContentLoaded', function() {
    // Tự động tải lại trang sạch sau 3 giây nếu gửi form thành công
    const successAlert = document.querySelector('.contact-form .alert-success');
    if (successAlert) {
        setTimeout(function() {
            window.location.href = 'contact.php';
        }, 3000);
    }

    // Hiệu ứng particle animation trên canvas
    const canvas = document.getElementById('background-canvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        
        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;
        
        const particles = [];
        const maxParticles = 50; // Giảm số lượng hạt để tối ưu vòng lặp O(N^2)
        
        function Particle(x, y, radius, color) {
            this.x = x;
            this.y = y;
            this.radius = radius;
            this.color = color;
            this.velocity = {
                x: (Math.random() - 0.5) * 0.5,
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
                
                if (this.x < 0 || this.x > width) this.velocity.x = -this.velocity.x;
                if (this.y < 0 || this.y > height) this.velocity.y = -this.velocity.y;
                
                this.draw();
            };
        }
        
        function init() {
            particles.length = 0;
            for (let i = 0; i < maxParticles; i++) {
                const radius = Math.random() * 2 + 1;
                const x = Math.random() * (width - radius * 2) + radius;
                const y = Math.random() * (height - radius * 2) + radius;
                const color = 'rgba(190, 190, 190, 0.5)';
                particles.push(new Particle(x, y, radius, color));
            }
        }
        
        function connectParticles() {
            for (let i = 0; i < particles.length; i++) {
                for (let j = i; j < particles.length; j++) {
                    const p1 = particles[i];
                    const p2 = particles[j];
                    const distance = Math.sqrt((p1.x - p2.x)**2 + (p1.y - p2.y)**2);
                    
                    const maxDistance = 120;
                    if (distance < maxDistance) {
                        ctx.beginPath();
                        ctx.moveTo(p1.x, p1.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.strokeStyle = `rgba(190, 190, 190, ${1 - (distance / maxDistance)})`;
                        ctx.lineWidth = 0.5;
                        ctx.stroke();
                    }
                }
            }
        }
        
        function animate() {
            requestAnimationFrame(animate);
            if (document.hidden) return; // Tạm dừng vẽ khi chuyển tab để tiết kiệm CPU/RAM
            
            ctx.clearRect(0, 0, width, height);
            
            connectParticles();
            
            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
            }
        }
        
        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
            init();
        });
        
        init();
        animate();
    }
});

