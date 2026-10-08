<style>
    /* =========================================================
   SYSTEM CONTENT
   ========================================================= */

    .system-content {
        width: 100%;
        max-width: 560px;
    }

    /* =========================================================
   BRAND
   ========================================================= */

    .brand {
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 52px;
    }

    .brand-icon {
        width: 44px;
        height: 44px;

        display: flex;
        align-items: center;
        justify-content: center;

        border: 1px solid var(--github-border);
        border-radius: 10px;

        background: var(--github-surface);
        color: var(--github-blue);

        font-size: 19px;

        box-shadow:
            0 1px 2px rgba(31, 35, 40, 0.04);

        transition:
            background-color 0.2s ease,
            border-color 0.2s ease,
            color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .brand:hover .brand-icon {
        border-color: var(--github-blue);
        box-shadow:
            0 3px 8px rgba(9, 105, 218, 0.08);
    }

    .brand-name {
        margin: 0;

        color: var(--github-text);

        font-size: 16px;
        line-height: 1.3;
        font-weight: 650;

        letter-spacing: -0.2px;
    }

    .brand-subtitle {
        margin: 3px 0 0;

        color: var(--github-muted);

        font-size: 12px;
        line-height: 1.4;
    }


    /* =========================================================
   SYSTEM INTRODUCTION
   ========================================================= */

    .system-title {
        max-width: 540px;

        margin: 0 0 20px;

        color: var(--github-text);

        font-size: clamp(36px, 3.4vw, 52px);
        line-height: 1.06;
        font-weight: 650;

        letter-spacing: -2.4px;
    }

    .system-title .text-primary {
        color: var(--github-blue) !important;
    }

    .system-description {
        max-width: 500px;

        margin: 0;

        color: var(--github-muted);

        font-size: 15px;
        line-height: 1.7;

        letter-spacing: -0.05px;
    }


    /* =========================================================
   DIVIDER
   ========================================================= */

    .system-divider {
        width: 100%;
        height: 1px;

        margin: 42px 0 30px;

        background: var(--github-border-light);
    }


    /* =========================================================
   FEATURE LIST
   ========================================================= */

    .system-features {
        display: flex;
        flex-direction: column;

        gap: 0;
    }

    .feature {
        display: flex;
        align-items: flex-start;

        gap: 15px;

        padding: 15px 0;

        border-bottom: 1px solid var(--github-border-light);

        transition:
            padding-left 0.2s ease,
            background-color 0.2s ease;
    }

    .feature:first-child {
        padding-top: 0;
    }

    .feature:last-child {
        border-bottom: 0;
    }


    /* =========================================================
   FEATURE ICON
   ========================================================= */

    .feature-icon {
        flex-shrink: 0;

        width: 34px;
        height: 34px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin-top: 1px;

        border: 1px solid var(--github-border-light);
        border-radius: 8px;

        background: var(--github-icon-bg);
        color: var(--github-text);

        font-size: 14px;

        transition:
            background-color 0.2s ease,
            border-color 0.2s ease,
            color 0.2s ease,
            transform 0.2s ease;
    }

    .feature:hover .feature-icon {
        border-color: var(--github-blue);
        background: var(--github-surface);
        color: var(--github-blue);

        transform: translateY(-1px);
    }


    /* =========================================================
   FEATURE CONTENT
   ========================================================= */

    .feature-title {
        margin: 0 0 3px;

        color: var(--github-text);

        font-size: 13px;
        line-height: 1.4;
        font-weight: 650;

        letter-spacing: -0.1px;
    }

    .feature-text {
        margin: 0;

        max-width: 440px;

        color: var(--github-muted);

        font-size: 12px;
        line-height: 1.55;
    }


    /* =========================================================
   SYSTEM FOOTER
   ========================================================= */

    .system-footer {
        margin-top: 34px;

        color: var(--github-subtle);

        font-size: 11px;
        line-height: 1.5;
    }
</style>


<div class="system-content">

    <!-- Brand -->
    <div class="brand">
        <div class="brand-icon">
            <i class="bi bi-box"></i>
        </div>
        <div>
            <p class="brand-name">
                FALP Nexus
            </p>

            <p class="brand-subtitle">
                Internal Web Template
            </p>
        </div>
    </div>

    <!-- Main System Information -->
    <h1 class="system-title">
        A unified foundation
        <span class="text-primary">for FALP web systems.</span>
    </h1>

    <p class="system-description">
        A centralized internal web template designed to provide FALP systems with a consistent, modern, and reusable foundation.
    </p>

    <div class="system-divider"></div>

    <!-- Features -->
    <div class="system-features">
        <!-- Reusable Components -->
        <div class="feature">
            <div class="feature-icon">
                <i class="bi bi-grid"></i>
            </div>

            <div>
                <p class="feature-title">
                    Reusable components
                </p>
                <p class="feature-text">
                    Ready-to-use components for faster system development.
                </p>
            </div>
        </div>

        <!-- Consistent Design -->
        <div class="feature">
            <div class="feature-icon">
                <i class="bi bi-palette"></i>
            </div>
            <div>
                <p class="feature-title">
                    Consistent design
                </p>
                <p class="feature-text">
                    A unified design language across FALP applications.
                </p>
            </div>
        </div>

        <!-- Responsive -->
        <div class="feature">
            <div class="feature-icon">
                <i class="bi bi-display"></i>
            </div>
            <div>
                <p class="feature-title">
                    Responsive by design
                </p>
                <p class="feature-text">
                    Optimized for desktop, laptop, and tablet devices.
                </p>
            </div>
        </div>

        <!-- Scalable -->
        <div class="feature">
            <div class="feature-icon">
                <i class="bi bi-layers"></i>
            </div>
            <div>
                <p class="feature-title">
                    Built for scalability
                </p>
                <p class="feature-text">
                    Flexible foundation for evolving system requirements.
                </p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="system-footer">
        © 2026 FALP Nexus · Internal Web Template
    </div>
</div>