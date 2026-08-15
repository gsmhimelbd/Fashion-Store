        </main>
    </div>

    <!-- Floating Global Admin Toast Notification Container -->
    <div id="adminToastContainer" class="fixed top-6 right-6 z-50 flex flex-col gap-2.5 max-w-sm pointer-events-none"></div>

    <!-- Universal Instant Save & AJAX Form Engine (Zero Full-Page Reload) -->
    <script>
        function showAdminToast(title, message, type = 'success') {
            const container = document.getElementById('adminToastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto p-4 rounded-2xl shadow-2xl border text-xs flex items-start gap-3 transition-all duration-300 transform translate-x-10 opacity-0 ${
                type === 'success' 
                    ? 'bg-slate-900/95 border-emerald-500/50 text-emerald-300 shadow-emerald-950/40' 
                    : 'bg-slate-900/95 border-rose-500/50 text-rose-300 shadow-rose-950/40'
            } backdrop-blur-md`;

            const icon = type === 'success' ? 'fa-circle-check text-emerald-400' : 'fa-circle-exclamation text-rose-400';

            toast.innerHTML = `
                <div class="text-base shrink-0 mt-0.5"><i class="fas ${icon}"></i></div>
                <div class="flex-1 min-w-0 space-y-0.5">
                    <strong class="font-bold text-white block">${title}</strong>
                    <p class="text-slate-300 text-[11px] leading-relaxed">${message}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white p-1 text-xs"><i class="fas fa-times"></i></button>
            `;

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-10', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            });

            setTimeout(() => {
                toast.classList.remove('translate-x-0', 'opacity-100');
                toast.classList.add('translate-x-10', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Intercept All Admin Form Submissions for Instant AJAX Save
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('form').forEach(form => {
                if (form.method && form.method.toUpperCase() === 'POST' && !form.hasAttribute('data-no-ajax')) {
                    form.addEventListener('submit', async (e) => {
                        // Allow standard confirm dialogs if present
                        if (form.getAttribute('onsubmit') && form.getAttribute('onsubmit').includes('confirm')) {
                            // If confirmation failed, return
                        }

                        e.preventDefault();

                        const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('input[type="submit"]');
                        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
                        
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> <span>Saving...</span>';
                        }

                        // Local Live Image Preview Update if file inputs exist
                        const fileInputs = form.querySelectorAll('input[type="file"]');
                        fileInputs.forEach(fileIn => {
                            if (fileIn.files && fileIn.files[0]) {
                                const reader = new FileReader();
                                reader.onload = (re) => {
                                    const previewImg = form.querySelector('img') || document.querySelector('img[src*="logo"]') || document.querySelector('img[src*="favicon"]');
                                    if (previewImg) previewImg.src = re.target.result;
                                };
                                reader.readAsDataURL(fileIn.files[0]);
                            }
                        });

                        try {
                            const formData = new FormData(form);
                            const actionUrl = form.action || window.location.href;

                            const res = await fetch(actionUrl, {
                                method: 'POST',
                                body: formData
                            });

                            const responseText = await res.text();

                            // Extract any server message or error
                            let msgTitle = 'Saved Successfully!';
                            let msgText = 'Your changes have been saved instantly without page reload.';
                            let isSuccess = true;

                            if (responseText.includes('bg-rose') || responseText.includes('Failed') || responseText.includes('Invalid')) {
                                msgTitle = 'Update Notice';
                                isSuccess = false;
                            }

                            // Extract exact message if rendered by PHP
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = responseText;
                            const greenBox = tempDiv.querySelector('.bg-emerald-500\\/20, .bg-emerald-50');
                            const redBox = tempDiv.querySelector('.bg-rose-500\\/20, .bg-rose-50');

                            if (greenBox) {
                                msgText = greenBox.textContent.trim();
                                isSuccess = true;
                            } else if (redBox) {
                                msgText = redBox.textContent.trim();
                                isSuccess = false;
                            }

                            if (submitBtn) {
                                submitBtn.innerHTML = isSuccess 
                                    ? '<i class="fas fa-check mr-1.5 text-emerald-300"></i> <span>Saved!</span>' 
                                    : '<i class="fas fa-exclamation-triangle mr-1.5 text-amber-300"></i> <span>Check Info</span>';
                                
                                setTimeout(() => {
                                    submitBtn.innerHTML = originalBtnHtml;
                                    submitBtn.disabled = false;
                                }, 2000);
                            }

                            showAdminToast(
                                isSuccess ? '✓ Instant Save Successful' : 'Notice', 
                                msgText, 
                                isSuccess ? 'success' : 'error'
                            );

                            // If this was an add item / create modal form, reset text inputs
                            if (form.classList.contains('ajax-reset-form') || form.getAttribute('data-reset') === 'true') {
                                form.reset();
                            }

                        } catch (err) {
                            if (submitBtn) {
                                submitBtn.innerHTML = originalBtnHtml;
                                submitBtn.disabled = false;
                            }
                            showAdminToast('Error', 'Failed to save: ' . (err.message || 'Network error'), 'error');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
