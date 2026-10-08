<style>
    @font-face {
        font-family: 'Poppins';
        src: url('dist/font/poppins/Poppins-Regular.ttf') format('truetype');
    }

    .poppins {
        font-family: 'Poppins', sans-serif;
    }

    @font-face {
        font-family: 'Mona Sans';
        src: url('../../dist/font/monasans/MonaSans-Regular.ttf') format('truetype');
    }

    .mona-sans {
        font-family: 'Mona Sans', sans-serif;
    }

    @font-face {
        font-family: 'Inter';
        src: url('dist/font/inter/Inter-Regular.ttf') format('truetype');
    }

    .inter {
        font-family: 'inter', sans-serif;
    }

    /* ====================================================================================================F */

    :root {
        --font-family: "Mona Sans", sans-serif;

        --fs-xxs: 10px;
        --fs-xs: 11px;
        --fs-sm: 12px;
        --fs-md: 13px;
        --fs-base: 14px;
        --fs-lg: 15px;
        --fs-xl: 20px;
        --fs-2xl: 24px;
        --fs-3xl: 30px;
        --fs-4xl: 36px;
    }


    body {
        font-family: var(--font-family);
        margin: 0;
        padding: 0;
    }

    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.16);
        border-radius: 999px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.28);
    }

    ::-webkit-scrollbar-thumb:active {
        background: rgba(0, 0, 0, 0.38);
    }

    ::-webkit-scrollbar-corner {
        background: transparent;
    }
</style>