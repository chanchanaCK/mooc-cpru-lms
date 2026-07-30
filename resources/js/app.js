import './bootstrap';

// Self-hosted fonts & icons (bundled by Vite — no external requests)
import 'bootstrap-icons/font/bootstrap-icons.css';
import '@fontsource/noto-sans-thai/300.css';
import '@fontsource/noto-sans-thai/400.css';
import '@fontsource/noto-sans-thai/500.css';
import '@fontsource/noto-sans-thai/600.css';
import '@fontsource/noto-sans-thai/700.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
