/* ============================================================
   StayU — main.js
   Global UI: navbar toggle, user dropdown, flash auto-dismiss
   ============================================================ */

(function () {
    'use strict';

    // ── Navbar mobile toggle ──────────────────────────────────
    const toggle  = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    if (toggle && navMenu) {
        toggle.addEventListener('click', function () {
            const isOpen = navMenu.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen);
        });

        // Close when a nav link is clicked (single-page feel on mobile)
        navMenu.querySelectorAll('.nav-links a').forEach(function (link) {
            link.addEventListener('click', function () {
                navMenu.classList.remove('open');
            });
        });
    }

    // ── User dropdown ─────────────────────────────────────────
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userDropdown   = document.getElementById('userDropdown');

    if (userMenuToggle && userDropdown) {
        userMenuToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            userDropdown.classList.toggle('open');
        });

        document.addEventListener('click', function () {
            userDropdown.classList.remove('open');
        });

        userDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // ── Notification bell dropdown ────────────────────────────
    const bellBtn       = document.getElementById('bellBtn');
    const notifDropdown = document.getElementById('notifDropdown');

    if (bellBtn && notifDropdown) {
        bellBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const opening = !notifDropdown.classList.contains('open');
            notifDropdown.classList.toggle('open');
            // Close user menu whenever bell opens
            if (opening && userDropdown) userDropdown.classList.remove('open');
        });

        document.addEventListener('click', function () {
            notifDropdown.classList.remove('open');
        });

        notifDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // ── Auto-dismiss flash alerts after 5 s ──────────────────
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .5s';
            el.style.opacity    = '0';
            setTimeout(function () { el.remove(); }, 500);
        }, 5000);
    });

    // ── Sticky navbar shrink on scroll ───────────────────────
    const navbar = document.getElementById('mainNav');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 60) {
                navbar.style.boxShadow = '0 2px 16px rgba(0,0,0,.12)';
            } else {
                navbar.style.boxShadow = '';
            }
        }, { passive: true });
    }

    // ── Tab panel helper (system_monitor, complaint_panel) ───
    document.querySelectorAll('[data-panel]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const group = btn.closest('.tabs, .filter-tabs');
            if (group) {
                group.querySelectorAll('[data-panel]').forEach(function (b) {
                    b.classList.remove('active');
                });
            }
            btn.classList.add('active');

            const target = document.getElementById(btn.dataset.panel);
            if (target) {
                document.querySelectorAll('.tab-content').forEach(function (p) {
                    p.classList.add('hidden');
                });
                target.classList.remove('hidden');
            }
        });
    });

    // ── Complaint table filter tabs ───────────────────────────
    document.querySelectorAll('.filter-tab[data-filter]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab[data-filter]').forEach(function (b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');

            const filter = btn.dataset.filter;
            document.querySelectorAll('#complaintTable tbody tr').forEach(function (row) {
                row.style.display =
                    (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
            });
        });
    });

    // ── Monitor tabs (system_monitor page) ───────────────────
    document.querySelectorAll('#monitorTabs .tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#monitorTabs .tab').forEach(function (b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(function (p) {
                p.classList.add('hidden');
            });
            const panel = document.getElementById(btn.dataset.panel);
            if (panel) panel.classList.remove('hidden');
        });
    });

    // ── Register page: tab switching ─────────────────────────
    document.querySelectorAll('.reg-tabs .role-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.reg-tabs .role-tab').forEach(function (b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
            document.querySelectorAll('.auth-form').forEach(function (f) {
                f.classList.add('hidden');
            });
            const target = document.getElementById(btn.dataset.target);
            if (target) target.classList.remove('hidden');
        });
    });

    // ── Login page: identifier label, placeholder and hint ──────────────────
    document.querySelectorAll('input[name="role"]').forEach(function (r) {
        r.addEventListener('change', function () {
            var cfg = {
                pelajar: {
                    label:       'Nombor Matrik',
                    placeholder: 'cth: A202584',
                    hint:        'Matrik pelajar UKM bermula dengan huruf A diikuti 6 digit.'
                },
                pemilik: {
                    label:       'Nombor IC',
                    placeholder: 'cth: 900101145555',
                    hint:        'Masukkan 12 digit nombor IC tanpa tanda pisah.'
                },
                admin: {
                    label:       'E-mel',
                    placeholder: 'cth: admin@stayu.ukm.my',
                    hint:        ''
                }
            };
            var c    = cfg[this.value];
            if (!c) return;
            var lbl  = document.getElementById('identifierLabel');
            var inp  = document.getElementById('identifier');
            var hint = document.getElementById('identifierHint');
            if (lbl)  lbl.textContent  = c.label;
            if (inp)  inp.placeholder  = c.placeholder;
            if (hint) hint.textContent = c.hint;
        });
    });

    // ── Password visibility toggle ────────────────────────────
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const inp = this.previousElementSibling;
            if (inp) inp.type = (inp.type === 'password') ? 'text' : 'password';
        });
    });

    // ── Photo preview (listing_form) ──────────────────────────
    const photoInput = document.getElementById('photoInput');
    if (photoInput) {
        photoInput.addEventListener('change', function () {
            const preview = document.getElementById('photoPreview');
            if (!preview) return;
            preview.innerHTML = '';
            Array.from(this.files).slice(0, 5).forEach(function (f) {
                const img   = document.createElement('img');
                img.src     = URL.createObjectURL(f);
                img.className = 'preview-thumb';
                preview.appendChild(img);
            });
        });
    }

    // ── Profile photo preview ─────────────────────────────────
    const profilePhotoInput = document.getElementById('profilePhotoInput');
    if (profilePhotoInput) {
        profilePhotoInput.addEventListener('change', function () {
            const preview = document.getElementById('profilePhotoPreview');
            if (!preview || !this.files[0]) return;
            preview.src = URL.createObjectURL(this.files[0]);
            preview.style.display = 'block';
        });
    }

    // ── Inline form-error helper ──────────────────────────────
    function showFormError(form, msg) {
        var existing = form.querySelector('.alert-form-error');
        if (existing) existing.remove();
        var div = document.createElement('div');
        div.className = 'alert alert-error alert-form-error';
        div.textContent = msg;
        form.insertBefore(div, form.firstChild);
        div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // ── Login form validation ─────────────────────────────────
    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            var roleEl  = document.querySelector('input[name="role"]:checked');
            var role    = roleEl ? roleEl.value : '';
            var idVal   = ((document.getElementById('identifier') || {}).value || '').trim();
            var passVal = (document.getElementById('password') || {}).value || '';
            var err = '';

            if (!role) {
                err = 'Sila pilih peranan (Pelajar / Pemilik / Admin).';
            } else if (!idVal) {
                err = 'Sila masukkan pengecam anda.';
            } else if (role === 'pelajar' && !/^[Aa]\d{6}$/.test(idVal)) {
                err = 'Nombor matrik mesti bermula dengan A diikuti 6 digit, contoh: A202584.';
            } else if (role === 'pemilik' && !/^\d{12}$/.test(idVal.replace(/[-\s]/g, ''))) {
                err = 'Nombor IC mesti mengandungi 12 digit, contoh: 900101145555.';
            } else if (role === 'admin' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(idVal)) {
                err = 'Sila masukkan alamat e-mel yang sah.';
            } else if (!passVal) {
                err = 'Kata laluan tidak boleh kosong.';
            }

            if (err) { e.preventDefault(); showFormError(loginForm, err); }
        });
    }

    // ── Register: student form validation ────────────────────
    var regStudentForm = document.getElementById('form-pelajar');
    if (regStudentForm) {
        regStudentForm.addEventListener('submit', function (e) {
            var matric = ((document.getElementById('s_matric') || {}).value || '').trim().toUpperCase();
            var pass   = (document.getElementById('s_pass')  || {}).value || '';
            var pass2  = (document.getElementById('s_pass2') || {}).value || '';
            var phone  = ((document.getElementById('s_phone') || {}).value || '').replace(/\s/g, '');
            var err = '';

            if (!/^A\d{6}$/.test(matric)) {
                err = 'Nombor matrik mesti bermula dengan A diikuti tepat 6 digit, contoh: A202584.';
            } else if (pass.length < 8) {
                err = 'Kata laluan mesti sekurang-kurangnya 8 aksara.';
            } else if (pass !== pass2) {
                err = 'Pengesahan kata laluan tidak sepadan.';
            } else if (!/^(\+?60|0)\d{8,10}$/.test(phone)) {
                err = 'Nombor telefon tidak sah, contoh: 0123456789.';
            }

            if (err) { e.preventDefault(); showFormError(regStudentForm, err); }
        });
    }

    // ── Register: owner form validation ──────────────────────
    var regOwnerForm = document.getElementById('form-pemilik');
    if (regOwnerForm) {
        regOwnerForm.addEventListener('submit', function (e) {
            var ic    = ((document.getElementById('o_ic')    || {}).value || '').replace(/[-\s]/g, '');
            var pass  = (document.getElementById('o_pass')  || {}).value || '';
            var pass2 = (document.getElementById('o_pass2') || {}).value || '';
            var phone = ((document.getElementById('o_phone') || {}).value || '').replace(/\s/g, '');
            var err = '';

            if (!/^\d{12}$/.test(ic)) {
                err = 'Nombor IC mesti mengandungi tepat 12 digit, contoh: 900101145555.';
            } else if (pass.length < 8) {
                err = 'Kata laluan mesti sekurang-kurangnya 8 aksara.';
            } else if (pass !== pass2) {
                err = 'Pengesahan kata laluan tidak sepadan.';
            } else if (!/^(\+?60|0)\d{8,10}$/.test(phone)) {
                err = 'Nombor telefon tidak sah, contoh: 0123456789.';
            }

            if (err) { e.preventDefault(); showFormError(regOwnerForm, err); }
        });
    }

    // ── Listing form: require coordinates before submit ───────
    var listingForm = document.querySelector('form.listing-form');
    if (listingForm) {
        listingForm.addEventListener('submit', function (e) {
            var lat  = ((document.getElementById('latitude')     || {}).value || '').trim();
            var lng  = ((document.getElementById('longitude')    || {}).value || '').trim();
            var rent = parseFloat((document.getElementById('monthly_rent') || {}).value || '0');
            var err  = '';

            if (!lat || !lng) {
                err = 'Sila tetapkan lokasi iklan pada peta (seret pin atau gunakan butang koordinat) sebelum menghantar.';
            } else if (rent <= 0) {
                err = 'Sewa bulanan mesti lebih daripada RM 0.';
            }

            if (err) { e.preventDefault(); showFormError(listingForm, err); }
        });
    }

})();
