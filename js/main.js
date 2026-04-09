// === Toàn bộ script đã gộp gọn, không lỗi ===
document.addEventListener('DOMContentLoaded', () => {
  /* ===============================
     0. Mobile Sidebar Toggle
  =============================== */
  const mobileSidebar = document.getElementById('mobileSidebar');
  const mobileToggle = document.getElementById('mobileMenuToggle');
  const mobileClose = document.getElementById('closeMobileSidebar');
  const mobileOverlay = document.getElementById('mobileSidebarOverlay');
  
  // Open mobile sidebar
  if (mobileToggle) {
    mobileToggle.addEventListener('click', () => {
      mobileSidebar?.classList.add('active');
      mobileOverlay?.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  }
  
  // Close mobile sidebar
  if (mobileClose) {
    mobileClose.addEventListener('click', () => {
      mobileSidebar?.classList.remove('active');
      mobileOverlay?.classList.remove('active');
      document.body.style.overflow = '';
    });
  }
  
  // Close on overlay click
  if (mobileOverlay) {
    mobileOverlay.addEventListener('click', () => {
      mobileSidebar?.classList.remove('active');
      mobileOverlay.classList.remove('active');
      document.body.style.overflow = '';
    });
  }
  
  // Close on ESC key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mobileSidebar?.classList.contains('active')) {
      mobileSidebar.classList.remove('active');
      mobileOverlay?.classList.remove('active');
      document.body.style.overflow = '';
    }
  });
  
  /* ===============================
     Mobile Submenu Toggle
  =============================== */
  const mobileSubmenuItems = document.querySelectorAll('.has-submenu-mobile > a');
  mobileSubmenuItems.forEach(item => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      const parent = item.parentElement;
      parent.classList.toggle('active');
    });
  });

  /* ===============================
     1. Main Slideshow Banner (Bootstrap Carousel)
  =============================== */
  const mainCarouselElement = document.getElementById('mainCarousel');
  if (mainCarouselElement) {
    // Khởi tạo Bootstrap Carousel bằng JavaScript để đảm bảo hoạt động
    const mainCarousel = new bootstrap.Carousel(mainCarouselElement, {
      interval: 5000, // Thời gian chuyển slide: 3 giây
      ride: 'carousel' // Tự động chạy khi tải trang
    });
  }

  /* ===============================
     2. Hiệu ứng đếm số (counter)
  =============================== */
  const counters = document.querySelectorAll('.stat-number');
  let hasScrolled = false;

  function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  const animateCount = (el) => {
    const target = +el.getAttribute('data-target');
    const prefix = el.getAttribute('data-prefix') || '';
    let start = 0;
    const duration = 2000;
    const increment = target / (duration / 10);

    const updateCount = () => {
      if (start < target) {
        start += increment;
        el.textContent = prefix + formatNumber(Math.floor(start));
        setTimeout(updateCount, 10);
      } else {
        el.textContent = prefix + formatNumber(target);
      }
    };
    updateCount();
  };

  const handleScroll = () => {
    if (!hasScrolled) {
      const container = document.querySelector('.transit-stats');
      if (container) {
        const rect = container.getBoundingClientRect();
        if (rect.top < window.innerHeight * 0.75 && rect.bottom >= 0) {
          counters.forEach(animateCount);
          hasScrolled = true;
          window.removeEventListener('scroll', handleScroll);
        }
      }
    }
  };
  window.addEventListener('scroll', handleScroll);
  handleScroll(); // chạy 1 lần khi load
  /* ===============================
     3. Gallery slideshow
  =============================== */
  // Chuyển đổi các gallery-item thành Bootstrap Carousels để có hiệu suất tốt hơn và nhất quán.
  // Điều này yêu cầu cấu trúc HTML của .gallery-item phải tuân thủ cấu trúc của Bootstrap Carousel.
  const galleryCarousels = document.querySelectorAll('.gallery-item.carousel');
  galleryCarousels.forEach(carouselEl => {
    new bootstrap.Carousel(carouselEl, {
      interval: 3000,
      ride: 'carousel'
    });
  });

  /* ===============================
     4. Chatbot “Chat Now” button
  =============================== */
  const openBtn = document.getElementById("open-chatbot");
  const closeBtn = document.getElementById("close-chatbot");
  const chatbotBox = document.getElementById("chatbot-box");
  const form = document.getElementById("chat-form");
  const successMsg = document.getElementById("success-message");

  if (openBtn && closeBtn && chatbotBox && form) {
    openBtn.addEventListener("click", () => chatbotBox.classList.add("show"));
    closeBtn.addEventListener("click", () => chatbotBox.classList.remove("show"));

    form.addEventListener("submit", (e) => {
      e.preventDefault();
      successMsg.style.display = "block";
      form.reset();
      setTimeout(() => {
        successMsg.style.display = "none";
        chatbotBox.classList.remove("show");
      }, 3000);
    });
  }
  /* ===============================
     5. News & Events Slider
  =============================== */
  (() => { // Sử dụng IIFE để đóng gói code, tránh xung đột biến
    const sliderWrapper = document.querySelector('.news-slider-wrapper');
    const slider = document.querySelector('.news-slider');
    const prevButton = document.querySelector('.slider-control.prev');
    const nextButton = document.querySelector('.slider-control.next');
    
    if (sliderWrapper && slider && prevButton && nextButton) {
        const slides = slider.querySelectorAll('.news-slide');
        if (slides.length === 0) return; // Không làm gì nếu không có slide

        let scrollAmount = 0;
        let autoScrollInterval;

        const calculateScrollAmount = () => {
            const slideWidth = slides[0].offsetWidth;
            const gap = 30; // Khoảng cách giữa các slide (từ CSS)
            scrollAmount = slideWidth + gap;
        };

        calculateScrollAmount(); // Tính toán lần đầu

        const startAutoScroll = () => {
            autoScrollInterval = setInterval(() => {
                // Nếu đã cuộn đến cuối, quay lại từ đầu
                if (slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 1) {
                    slider.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                }
            }, 5000); // Tự động cuộn mỗi 5 giây
        };

        const stopAutoScroll = () => {
            clearInterval(autoScrollInterval);
        };

        // Xử lý khi nhấn nút Next
        nextButton.addEventListener('click', () => {
            slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });

        // Xử lý khi nhấn nút Prev
        prevButton.addEventListener('click', () => {
            slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });

        // Tính toán lại khi thay đổi kích thước cửa sổ để responsive
        // Giữ lại stop/start ở đây để slider hoạt động đúng sau khi resize
        window.addEventListener('resize', () => {
            stopAutoScroll();
            calculateScrollAmount();
            startAutoScroll();
        });

        // Bắt đầu tự động chạy
        startAutoScroll();
    }
  })();
});
