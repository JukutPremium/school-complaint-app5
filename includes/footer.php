    </div>

    <footer class="bg-white border-t border-gray-200 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-gray-600 text-xs sm:text-sm">
                &copy; <?php echo date('Y'); ?> Aplikasi Pengaduan Sekolah. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');
                const icon = this.querySelector('i');
                if (mobileMenu.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-auto-hide');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });

        function confirmDelete(message) {
            return confirm(message || 'Apakah Anda yakin ingin menghapus data ini?');
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview-image');
                    const previewContainer = document.getElementById('preview-container');
                    if (preview && previewContainer) {
                        preview.src = e.target.result;
                        previewContainer.classList.remove('hidden');
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        const fileUploadBox = document.querySelector('.file-upload-box');
        if (fileUploadBox) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                fileUploadBox.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                fileUploadBox.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                fileUploadBox.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                fileUploadBox.classList.add('drag-over');
            }

            function unhighlight(e) {
                fileUploadBox.classList.remove('drag-over');
            }

            fileUploadBox.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                const input = document.querySelector('input[type="file"]');
                if (input) {
                    input.files = files;
                    previewImage(input);
                }
            }
        }
    </script>
</body>
</html>
