document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // 1. XỬ LÝ NAVBAR & SIDEBAR MOBILE
    // ==========================================
    const menuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('mobileSidebar');
    const closeBtn = document.getElementById('closeMobileSidebar');
    const overlay = document.getElementById('mobileSidebarOverlay');

    function toggleSidebar() {
        if (sidebar && overlay) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    }

    if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
    if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', toggleSidebar);

    // Xử lý dropdown (sổ xuống) cho các menu con trên mobile
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault(); // Ngăn chặn chuyển trang khi nhấn vào toggle
            this.parentElement.classList.toggle('open'); // Thêm/bỏ class open để hiện/ẩn menu con
        });
    });

    // ==========================================
    // 2. XỬ LÝ NÚT LIÊN HỆ NHANH (CONTACT BUTTONS)
    // ==========================================
    const contactToggleBtn = document.getElementById('contactToggleBtn');
    const contactWrapper = document.getElementById('contactWrapper');
    
    if (contactToggleBtn && contactWrapper) {
        contactToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Ngăn sự kiện nổi bọt
            contactWrapper.classList.toggle('show');
        });

        // Nhấn ra ngoài màn hình để đóng menu
        document.addEventListener('click', function(e) {
            if (!contactWrapper.contains(e.target) && contactWrapper.classList.contains('show')) {
                contactWrapper.classList.remove('show');
            }
        });
    }

    // ==========================================
    // 3. KHỞI TẠO AOS, SLIDER TIN TỨC & LOGO ĐỐI TÁC
    // ==========================================
    
    // Khởi tạo hiệu ứng AOS (Mờ dần khi cuộn trang tới)
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 1000, once: true });
    }

    // Slider Tin Tức
    const newsSlider = document.querySelector('.news-slider');
    const newsWrapper = document.querySelector('.news-slider-wrapper');
    const prevBtn = document.querySelector('.news-control.prev');
    const nextBtn = document.querySelector('.news-control.next');

    if (newsSlider && newsWrapper && prevBtn && nextBtn) {
        let isNewsHovered = false;
        newsWrapper.addEventListener('mouseenter', () => isNewsHovered = true);
        newsWrapper.addEventListener('mouseleave', () => isNewsHovered = false);
        // Tối ưu thao tác chạm trên điện thoại
        newsWrapper.addEventListener('touchstart', () => isNewsHovered = true, {passive: true});
        newsWrapper.addEventListener('touchend', () => setTimeout(() => isNewsHovered = false, 1000), {passive: true});

        // Tối ưu hiệu năng: Chỉ chạy animation khi slider nằm trong màn hình
        let isNewsVisible = true;
        if (window.IntersectionObserver) {
            const observer = new IntersectionObserver((entries) => {
                isNewsVisible = entries[0].isIntersecting;
            });
            observer.observe(newsWrapper);
        }

        const newsDirection = 'rtl'; // 'rtl': Từ phải sang trái | 'ltr': Từ trái sang phải

        function autoScrollNews() {
            if (isNewsVisible && !isNewsHovered && newsSlider.scrollWidth > newsSlider.clientWidth) {
                if (newsDirection === 'rtl') {
                    newsSlider.scrollLeft += 1;
                    // Dùng Math.ceil để tránh lỗi kẹt pixel lẻ
                    if (Math.ceil(newsSlider.scrollLeft + newsSlider.clientWidth) >= newsSlider.scrollWidth) {
                        newsSlider.scrollLeft = 0;
                    }
                } else {
                    newsSlider.scrollLeft -= 1;
                    if (newsSlider.scrollLeft <= 0) {
                        newsSlider.scrollLeft = newsSlider.scrollWidth - newsSlider.clientWidth;
                    }
                }
            }
            requestAnimationFrame(autoScrollNews);
        }
        requestAnimationFrame(autoScrollNews);

        nextBtn.addEventListener('click', () => newsSlider.scrollBy({ left: 380, behavior: 'smooth' }));
        prevBtn.addEventListener('click', () => newsSlider.scrollBy({ left: -380, behavior: 'smooth' }));
    }

    // Logo đối tác trượt ngang vô tận
    const partnersLogos = document.querySelector('.partners-logos');
    if (partnersLogos) {
        partnersLogos.style.animation = 'none';
        let isLogoHovered = false;
        partnersLogos.addEventListener('mouseenter', () => isLogoHovered = true);
        partnersLogos.addEventListener('mouseleave', () => isLogoHovered = false);
        // Xử lý chống kẹt hiệu ứng khi chạm tay trên điện thoại di động
        partnersLogos.addEventListener('touchstart', () => isLogoHovered = true, {passive: true});
        partnersLogos.addEventListener('touchend', () => setTimeout(() => isLogoHovered = false, 1000), {passive: true});

        // Tối ưu hiệu năng: Chỉ chạy animation khi slider nằm trong màn hình
        let isLogoVisible = true;
        if (window.IntersectionObserver) {
            const observer = new IntersectionObserver((entries) => {
                isLogoVisible = entries[0].isIntersecting;
            });
            observer.observe(partnersLogos.parentElement || partnersLogos);
        }

        let currentX = 0;
        const speed = 1; // Tốc độ trượt
        const logoDirection = 'rtl'; // 'rtl': Từ phải sang trái | 'ltr': Từ trái sang phải
        
        function animateLogos() {
            if (isLogoVisible && !isLogoHovered) {
                const firstLogo = partnersLogos.firstElementChild;
                const secondLogo = firstLogo ? firstLogo.nextElementSibling : null;
                const lastLogo = partnersLogos.lastElementChild;
                
                if (firstLogo && secondLogo && lastLogo) {
                    const firstRect = firstLogo.getBoundingClientRect();
                    const secondRect = secondLogo.getBoundingClientRect();
                    const containerRect = partnersLogos.parentElement.getBoundingClientRect();
                    const itemWidthAndGap = secondRect.left - firstRect.left; // Chiều rộng 1 logo + khoảng cách
                    
                    if (logoDirection === 'rtl') {
                        currentX -= speed;
                        if (firstRect.width > 0 && firstRect.right <= 0) {
                            partnersLogos.appendChild(firstLogo);
                            currentX += itemWidthAndGap;
                        }
                    } else {
                        currentX += speed;
                        if (firstRect.width > 0 && firstRect.left >= containerRect.left) {
                            partnersLogos.prepend(lastLogo);
                            currentX -= itemWidthAndGap;
                        }
                    }
                }
                partnersLogos.style.transform = `translateX(${currentX}px)`;
            }
            requestAnimationFrame(animateLogos);
        }
        requestAnimationFrame(animateLogos);
    }

    // ==========================================
    // 4. HIỆU ỨNG CHẠY SỐ TỰ ĐỘNG (COUNTER UP)
    // ==========================================
    const stats = document.querySelectorAll('.stat-number');
    const statsSection = document.querySelector('.transit-stats');
    let hasAnimatedStats = false;

    function animateStats() {
        if (hasAnimatedStats) return;
        hasAnimatedStats = true; // Ngăn việc chạy nhiều lần

        stats.forEach(stat => {
            const target = +stat.getAttribute('data-target');
            const prefix = stat.getAttribute('data-prefix') || '';
            const duration = 2000; // Thời gian chạy (2 giây)
            const increment = target / (duration / 16); 

            let current = 0;
            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    stat.innerText = prefix + Math.ceil(current).toLocaleString('vi-VN');
                    requestAnimationFrame(updateCounter);
                } else {
                    stat.innerText = prefix + target.toLocaleString('vi-VN');
                }
            };
            updateCounter();
        });
    }

    if (statsSection && stats.length > 0) {
        const checkScroll = () => {
            if (hasAnimatedStats) {
                window.removeEventListener('scroll', checkScroll);
                return;
            }
            const rect = statsSection.getBoundingClientRect();
            
            // Kích hoạt khi khu vực lọt vào khung hình 
            if (rect.top < window.innerHeight - 50 && rect.bottom > 0) {
                animateStats();
                window.removeEventListener('scroll', checkScroll);
            }
        };
        
        // Tích hợp lại IntersectionObserver (Cách ưu việt và chính xác nhất cho số chạy)
        if (window.IntersectionObserver) {
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && !hasAnimatedStats) {
                    animateStats();
                    observer.disconnect();
                    window.removeEventListener('scroll', checkScroll);
                }
            }, { threshold: 0.1 }); // Xuất hiện 10% màn hình là kích hoạt
            observer.observe(statsSection);
        }

        setTimeout(checkScroll, 100);
        window.addEventListener('scroll', checkScroll, {passive: true});
    }

    // Trì hoãn khởi tạo hệ thống Chat 2 giây để nhường băng thông cho giao diện chính
    setTimeout(() => {
    // ==========================================
    // 5. XỬ LÝ LIVE CHAT WIDGET
    // ==========================================
    const chatBtn = document.getElementById('futaChatBtn');
    const chatBox = document.getElementById('futaChatBox');
    const closeBox = document.getElementById('closeChatBox');
    const resetBtn = document.getElementById('resetChatBtn');
    const chatBody = document.getElementById('futaChatBody');
    const registerContainer = document.getElementById('chatRegisterContainer');
    const inputArea = document.getElementById('chatInputArea');
    const msgContainer = document.getElementById('futaChatMessages');
    const chatInput = document.getElementById('chatInputMsg');
    const sendBtn = document.getElementById('sendChatBtn');
    const attachBtn = document.getElementById('attachChatBtn');
    const attachInput = document.getElementById('chatAttachFile');

    if (chatBtn && chatBox) {
        let sessionId = localStorage.getItem('futa_chat_session') || null;
        let customerName = localStorage.getItem('futa_chat_name') || '';
        let lastMsgId = 0;
        let lastRenderedDate = '';
        
        // --- KẾT NỐI WEBSOCKET ---
        let ws = null;
        let isWsConnected = false;
        function connectWebSocket() {
            // Cấu hình WebSocket URL cố định - Dễ dàng thay đổi khi deploy
            // - Localhost: 'ws://localhost:8080'
            // - Production (với domain và SSL): 'wss://yourdomain.com/chat/' (Lưu ý: /chat/ là path đã được cấu hình Reverse Proxy trên IIS/Nginx)
            const wsUrl = 'wss://futa-advertising.com/chat/'; // <<<< THAY ĐỔI THÀNH ĐÚNG DOMAIN CỦA BẠN

            ws = new WebSocket(wsUrl);
            ws.onopen = () => { isWsConnected = true; };
            ws.onmessage = (e) => {
                const data = JSON.parse(e.data);
                if (data.event === 'new_message' && data.session_id == sessionId) {
                    fetchMessages(); // Nhận được tín hiệu có tin nhắn mới -> Tải hiển thị ngay lập tức
                }
            };
            ws.onerror = (e) => console.error('WebSocket Error:', e);
            ws.onclose = () => {
                isWsConnected = false;
                setTimeout(connectWebSocket, 5000); // Tự động kết nối lại sau 5s nếu mất kết nối
            };
        }
        connectWebSocket();

        // --- FALLBACK POLLING ---
        setInterval(() => {
            if (sessionId && !isWsConnected && !document.hidden) {
                fetchMessages();
            }
        }, 3000);

        // Bật tắt khung chat
        chatBtn.addEventListener('click', () => {
            chatBox.classList.toggle('show');
            if(chatBox.classList.contains('show')) {
                if(sessionId) {
                    registerContainer.style.display = 'none'; // Ẩn form
                    msgContainer.style.display = 'flex'; // Hiện vùng tin nhắn
                    inputArea.style.display = 'flex'; // Hiện vùng nhập liệu
                    if (customerName) {
                        document.querySelector('.futa-header-title .title-main').textContent = `Xin chào, ${customerName}`;
                    }
                    resetBtn.style.display = 'inline-block';
                    fetchMessages(true); // Luôn cuộn xuống khi mở lại
                } else {
                    // Trạng thái ban đầu, form đăng ký đã hiển thị mặc định
                }
            }
        });

        closeBox.addEventListener('click', () => {
            chatBox.classList.remove('show');
        });

        // Xử lý nút Làm mới (Reset) phiên chat
        resetBtn.addEventListener('click', () => {
            if (confirm('Bạn có muốn kết thúc phiên chat hiện tại và bắt đầu lại không?')) {
                localStorage.removeItem('futa_chat_session');
                localStorage.removeItem('futa_chat_name');
                sessionId = null;
                customerName = '';
                lastRenderedDate = '';
                msgContainer.innerHTML = ''; // Xóa sạch tin nhắn
                msgContainer.style.display = 'none'; // Ẩn vùng tin nhắn
                inputArea.style.display = 'none'; // Ẩn vùng nhập liệu
                registerContainer.style.display = 'flex'; // Hiện lại form đăng ký
                resetBtn.style.display = 'none'; // Ẩn nút reset                
                // Khôi phục tiêu đề gốc
                document.querySelector('.futa-header-title .title-main').setAttribute('data-i18n', 'about.chat_title');
                document.querySelector('.futa-header-title .title-sub').style.display = 'block';
                if (window.i18n) window.i18n.setLanguage(window.i18n.getLanguage()); 
            }
        });

        // Bắt đầu phiên chat mới
        const startChatBtn = document.getElementById('startChatBtn');
        if (startChatBtn) {
            startChatBtn.addEventListener('click', async () => {
                const name = document.getElementById('chatName').value.trim();
                const phone = document.getElementById('chatPhone').value.trim();
                const email = document.getElementById('chatEmail').value.trim();
                const initialMessage = document.getElementById('chatInitialMessage').value.trim();
                if(!name || !phone) return alert('Vui lòng nhập Họ tên và Số điện thoại!');

                const fd = new URLSearchParams();
                fd.append('action', 'start_session'); fd.append('name', name); fd.append('phone', phone); fd.append('email', email); fd.append('initial_message', initialMessage);

                const res = await fetch('includes/contact-chat-api.php', { method: 'POST', body: fd }).then(r => r.json());
                if(res.success) {
                    sessionId = res.session_id;
                    customerName = name; 
                    localStorage.setItem('futa_chat_session', sessionId);
                    localStorage.setItem('futa_chat_name', customerName);
                    registerContainer.style.display = 'none'; // Ẩn toàn bộ container đăng ký
                    msgContainer.style.display = 'flex';    // Hiện vùng tin nhắn
                    inputArea.style.display = 'flex'; // Hiện vùng nhập liệu
                    // Cập nhật tiêu đề khi có tên khách hàng
                    document.querySelector('.futa-header-title .title-main').textContent = `Xin chào, ${customerName}`;
                    document.querySelector('.futa-header-title .title-sub').style.display = 'none'; // Ẩn dòng phụ
                    resetBtn.style.display = 'inline-block';
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ event: 'new_session' })); 
                    }
                    
                    fetchMessages(true); 
                }
            });
        }

        // Gửi và Lấy tin nhắn
        const sendMessage = async () => {
            const msg = chatInput.value.trim();
            if(!msg || !sessionId) return;
            
            chatInput.value = ''; 
            
            const tempDiv = document.createElement('div');
            tempDiv.className = 'futa-chat-message customer temp-msg';
            tempDiv.textContent = msg;
            tempDiv.style.opacity = '0.6';
            chatBody.appendChild(tempDiv); // Thêm vào vùng cuộn chính
            msgContainer.scrollTop = msgContainer.scrollHeight;

            const fd = new URLSearchParams();
            fd.append('action', 'send_message'); fd.append('session_id', sessionId); fd.append('sender', 'customer'); fd.append('message', msg);
            
            try {
                const res = await fetch('includes/contact-chat-api.php', { method: 'POST', body: fd }).then(r => r.json());
                if(res.success) {
                    tempDiv.remove(); 
                    await fetchMessages(true); 
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ event: 'new_message', session_id: sessionId })); 
                    }
                }
            } catch (e) {
                console.error(e);
                tempDiv.style.border = '1px solid red';
            }
        };

        sendBtn.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', (e) => e.key === 'Enter' && sendMessage());

        // Xử lý gửi file
        attachBtn.addEventListener('click', () => attachInput.click());
        attachInput.addEventListener('change', async function() {
            if(this.files.length > 0 && sessionId) {
                const file = this.files[0];
                let maxSize = 15 * 1024 * 1024; // Mặc định 15MB
                if (file.type.startsWith('video/')) maxSize = 30 * 1024 * 1024;
                else if (file.type.startsWith('image/')) maxSize = 5 * 1024 * 1024;
                
                if (file.size > maxSize) return alert('File quá lớn (Ảnh: Tối đa 5MB, Video: Tối đa 30MB, File khác: Tối đa 15MB)');
                
                const tempDiv = document.createElement('div');
                tempDiv.className = 'futa-chat-message customer temp-msg';
                tempDiv.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang gửi file...';
                tempDiv.style.opacity = '0.6';
                chatBody.appendChild(tempDiv); // Thêm vào vùng cuộn chính
                msgContainer.scrollTop = msgContainer.scrollHeight;

                const fd = new FormData();
                fd.append('action', 'send_message');
                fd.append('session_id', sessionId);
                fd.append('sender', 'customer');
                fd.append('file', file);
                
                try {
                    const res = await fetch('includes/contact-chat-api.php', { method: 'POST', body: fd }).then(r => r.json());
                    if(res.success) {
                        tempDiv.remove();
                        await fetchMessages(true);
                        if (ws && ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({ event: 'new_message', session_id: sessionId }));
                    } else {
                        tempDiv.innerHTML = '<i class="bi bi-exclamation-circle text-danger"></i> ' + res.error;
                    }
                } catch (e) {
                    tempDiv.style.border = '1px solid red';
                }
                this.value = ''; 
            }
        });

        // Hàm Format hiển thị ảnh/video/tài liệu
        const formatChatMessage = (rawMsg) => {
            if (rawMsg.startsWith('FILE::')) {
                const parts = rawMsg.split('::');
                if (parts.length >= 4) {
                    const mime = parts[1]; const url = parts[2]; const name = parts[3];
                    if (mime.startsWith('image/')) {
                        return `<a href="${url}" target="_blank"><img src="${url}" alt="${name}" style="max-width: 100%; border-radius: 8px; margin-top: 5px;"></a>`;
                    } else if (mime.startsWith('video/')) {
                        return `<video controls src="${url}" style="max-width: 100%; border-radius: 8px; margin-top: 5px;"></video>`;
                    } else {
                        return `<a href="${url}" target="_blank" style="text-decoration: none; color: inherit; display:flex; align-items:center; gap:5px;"><i class="bi bi-file-earmark-arrow-down fs-5"></i> ${name}</a>`;
                    }
                }
            }
            const div = document.createElement('div');
            div.textContent = rawMsg;
            return div.innerHTML.replace(/\n/g, '<br>');
        };

        const fetchMessages = async (forceScroll = false) => {
            if(!sessionId) return;
            const t = new Date().getTime(); // Chống cache
            const res = await fetch(`includes/contact-chat-api.php?action=get_messages&session_id=${sessionId}&last_id=${lastMsgId}&t=${t}`).then(r => r.json());
            if(res.success && res.messages.length > 0) {
                const shouldScroll = forceScroll || (chatBody.scrollTop + chatBody.clientHeight >= chatBody.scrollHeight - 50);
                
                if (lastMsgId === 0) {
                    msgContainer.innerHTML = '';
                    lastRenderedDate = '';
                }

                res.messages.forEach(m => {
                    const msgDateObj = new Date(m.created_at.replace(' ', 'T'));
                    const msgDateStr = msgDateObj.toLocaleDateString('vi-VN');
                    const msgTimeStr = msgDateObj.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});

                    if (msgDateStr !== lastRenderedDate) {
                        const dateDiv = document.createElement('div');
                        dateDiv.className = 'futa-chat-date-separator';
                        
                        const todayStr = new Date().toLocaleDateString('vi-VN');
                        const yesterdayObj = new Date();
                        yesterdayObj.setDate(yesterdayObj.getDate() - 1);
                        const yesterdayStr = yesterdayObj.toLocaleDateString('vi-VN');

                        if (msgDateStr === todayStr) dateDiv.textContent = 'Hôm nay';
                        else if (msgDateStr === yesterdayStr) dateDiv.textContent = 'Hôm qua';
                        else dateDiv.textContent = msgDateStr;
                        
                        msgContainer.appendChild(dateDiv);
                        lastRenderedDate = msgDateStr;
                    }

                    const div = document.createElement('div');
                    div.className = `futa-chat-message ${m.sender}`;
                    div.innerHTML = formatChatMessage(m.message) + `<div class="futa-chat-time">${msgTimeStr}</div>`;
                    msgContainer.appendChild(div);
                    lastMsgId = m.id;
                });
                if (shouldScroll) {
                    chatBody.scrollTop = chatBody.scrollHeight;
                }
            }
        };
    }
    }, 2000); // Kết thúc setTimeout trì hoãn chat widget
});