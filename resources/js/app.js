import Alpine from 'alpinejs';
import {
    createElement,
    createIcons,
    Activity,
    Archive,
    ArrowDown,
    ArrowLeft,
    ArrowRight,
    ArrowUp,
    Award,
    BadgeCheck,
    BookOpen,
    Briefcase,
    Building2,
    Calendar,
    ChevronDown,
    ChevronRight,
    CircleCheck,
    CircleX,
    ClipboardList,
    Clock,
    CornerDownLeft,
    CreditCard,
    Download,
    ExternalLink,
    Eye,
    FileText,
    Globe,
    GraduationCap,
    Inbox,
    IndianRupee,
    Info,
    KeyRound,
    Layers,
    LayoutDashboard,
    Lightbulb,
    Lock,
    LogOut,
    Mail,
    MapPin,
    Menu,
    MessageSquare,
    Monitor,
    Moon,
    PanelLeftClose,
    PanelLeftOpen,
    Pencil,
    Phone,
    Plus,
    Printer,
    QrCode,
    Search,
    Send,
    Settings,
    ShieldCheck,
    ShieldX,
    ShoppingCart,
    Star,
    Sun,
    Tags,
    Target,
    Trash2,
    TrendingUp,
    TriangleAlert,
    Upload,
    User,
    UserPlus,
    Users,
    X,
} from 'lucide';

const icons = {
    Activity, Archive, ArrowDown, ArrowLeft, ArrowRight, ArrowUp, Award, BadgeCheck, BookOpen, Briefcase, Building2, Calendar,
    ChevronDown, ChevronRight, CircleCheck, CircleX, ClipboardList, Clock, CornerDownLeft, CreditCard, Download, ExternalLink, Eye, FileText,
    Globe, GraduationCap, Inbox, IndianRupee, Info, KeyRound, Layers, LayoutDashboard, Lightbulb, Lock, LogOut,
    Mail, MapPin, Menu, MessageSquare, Monitor, Moon, PanelLeftClose, PanelLeftOpen, Pencil, Phone, Plus, Printer, QrCode, Search, Send, Settings,
    ShieldCheck, ShieldX, ShoppingCart, Star, Sun, Tags, Target, Trash2, TrendingUp, TriangleAlert, Upload, User, UserPlus, Users, X,
};

const renderIcons = () => createIcons({ icons });

renderIcons();

// Render icons inside content Alpine adds later (teleported dialogs, toggled sections).
new MutationObserver(() => {
    if (document.querySelector('i[data-lucide]')) {
        renderIcons();
    }
}).observe(document.body, { childList: true, subtree: true });

/**
 * Live total / percentage / grade for a row of the marks grid.
 */
Alpine.data('marksRow', (modules, initial, bands) => ({
    marks: initial,
    parts(module) {
        return ['theory', 'practical'].filter((part) => module[part].max > 0);
    },
    filled(value) {
        return value !== '' && value !== null && value !== undefined;
    },
    get complete() {
        return modules.every((m) => this.parts(m).every((part) => this.filled(this.marks[m.id][part])));
    },
    get total() {
        return modules.reduce((sum, m) => sum + this.parts(m).reduce((partSum, part) => partSum + (parseFloat(this.marks[m.id][part]) || 0), 0), 0);
    },
    get max() {
        return modules.reduce((sum, m) => sum + m.theory.max + m.practical.max, 0);
    },
    get percentage() {
        return this.max ? (this.total / this.max) * 100 : 0;
    },
    get grade() {
        if (!this.complete) return '—';
        const failedModule = modules.some((m) => this.parts(m).some((part) => parseFloat(this.marks[m.id][part]) < m[part].pass));
        if (failedModule) return 'Fail';
        const band = bands.find((b) => this.percentage >= b.min);
        return band ? band.grade : 'Fail';
    },
    invalid(moduleId, part) {
        const module = modules.find((m) => m.id === moduleId);
        const value = this.marks[moduleId][part];
        if (!this.filled(value)) return false;
        const number = parseFloat(value);
        return Number.isNaN(number) || number < 0 || number > module[part].max;
    },
}));

/**
 * SVG markup for a lucide icon by its kebab-case name, for content Alpine renders reactively.
 */
const iconMarkup = (name, attributes = {}) => {
    const pascal = String(name ?? '').replace(/(^|-)([a-z0-9])/g, (match, dash, letter) => letter.toUpperCase());
    const node = icons[pascal] ?? icons.ArrowRight;

    return createElement(node, { width: 16, height: 16, 'aria-hidden': 'true', ...attributes }).outerHTML;
};

const isTyping = (element) => element instanceof HTMLElement
    && (element.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(element.tagName));

/*
 * Admin desk preferences. The admin layout's head script applies the stored values
 * before first paint; these stores keep the controls in sync afterwards.
 */
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        try { localStorage.setItem('admin-theme', this.dark ? 'dark' : 'light'); } catch (e) { /* storage unavailable */ }
    },
});

Alpine.store('sidebar', {
    collapsed: document.documentElement.dataset.sidebar === 'collapsed',
    toggle() {
        this.collapsed = !this.collapsed;
        document.documentElement.dataset.sidebar = this.collapsed ? 'collapsed' : 'expanded';
        try { localStorage.setItem('admin-sidebar', this.collapsed ? 'collapsed' : 'expanded'); } catch (e) { /* storage unavailable */ }
    },
});

Alpine.store('palette', {
    open: false,
    shortcut: /Mac|iPhone|iPad/.test(navigator.userAgent) ? '⌘ K' : 'Ctrl K',
    show() { this.open = true; },
    hide() { this.open = false; },
});

/**
 * Admin command palette: jump to a page, start a "new" action, change a preference,
 * or find a student, course, enrollment or certificate by name or number.
 */
Alpine.data('commandPalette', (config) => ({
    query: '',
    active: 0,
    records: [],
    loading: false,
    failed: false,
    request: null,
    returnFocus: null,

    init() {
        this.$watch('$store.palette.open', (open) => {
            if (open) {
                this.returnFocus = document.activeElement;
                this.query = '';
                this.records = [];
                this.failed = false;
                this.active = 0;
                this.$nextTick(() => this.$refs.input.focus());

                return;
            }

            this.request?.abort();
            this.loading = false;
            this.returnFocus?.focus?.();
        });
    },

    onGlobalKeydown(event) {
        const key = event.key?.toLowerCase();

        if ((event.metaKey || event.ctrlKey) && key === 'k') {
            event.preventDefault();
            this.$store.palette.open ? this.$store.palette.hide() : this.$store.palette.show();
        } else if (key === '/' && !this.$store.palette.open && !isTyping(event.target)) {
            event.preventDefault();
            this.$store.palette.show();
        }
    },

    get commands() {
        const theme = this.$store.theme;
        const sidebar = this.$store.sidebar;

        return [
            ...config.actions.map((action) => ({ ...action, group: 'Create' })),
            ...config.pages.map((page) => ({ ...page, group: 'Go to' })),
            { group: 'Preferences', title: theme.dark ? 'Switch to light mode' : 'Switch to dark mode', icon: theme.dark ? 'sun' : 'moon', run: () => theme.toggle() },
            ...(window.matchMedia('(min-width: 1024px)').matches
                ? [{ group: 'Preferences', title: sidebar.collapsed ? 'Expand the sidebar' : 'Collapse the sidebar', icon: sidebar.collapsed ? 'panel-left-open' : 'panel-left-close', run: () => sidebar.toggle() }]
                : []),
            { group: 'Preferences', title: 'View website', subtitle: 'Opens in a new tab', icon: 'globe', url: config.homeUrl, newTab: true },
            { group: 'Preferences', title: 'Log out', icon: 'log-out', run: () => document.getElementById('admin-logout-form')?.submit() },
        ];
    },

    get groups() {
        const term = this.query.trim().toLowerCase();
        const matches = (item) => !term || `${item.title} ${item.subtitle ?? ''}`.toLowerCase().includes(term);
        const items = [...(term ? this.records : []), ...this.commands.filter(matches)];
        const grouped = new Map();
        let index = 0;

        items.forEach((item) => {
            if (!grouped.has(item.group)) {
                grouped.set(item.group, []);
            }
            grouped.get(item.group).push({ ...item, index: index++, key: `${item.group}|${item.url ?? item.title}` });
        });

        return [...grouped].map(([label, groupItems]) => ({ label, items: groupItems }));
    },

    get flat() {
        return this.groups.flatMap((group) => group.items);
    },

    /** Text for the palette's polite live region. */
    get announcement() {
        if (this.loading) {
            return 'Searching records…';
        }

        if (this.failed) {
            return 'Record search is unavailable.';
        }

        const count = this.flat.length;

        return this.query.trim() ? `${count} ${count === 1 ? 'result' : 'results'}` : '';
    },

    async search() {
        const term = this.query.trim();
        this.request?.abort();

        if (term.length < 2) {
            this.records = [];
            this.loading = false;

            return;
        }

        const request = new AbortController();
        this.request = request;
        this.loading = true;
        this.failed = false;

        try {
            const response = await fetch(`${config.searchUrl}?q=${encodeURIComponent(term)}`, {
                headers: { Accept: 'application/json' },
                signal: request.signal,
            });

            if (!response.ok) {
                throw new Error(`Search failed with status ${response.status}`);
            }

            this.records = (await response.json()).results ?? [];
            this.active = 0;
        } catch (error) {
            if (error.name !== 'AbortError') {
                this.records = [];
                this.failed = true;
            }
        } finally {
            if (this.request === request) {
                this.loading = false;
            }
        }
    },

    move(step) {
        const count = this.flat.length;

        if (!count) {
            return;
        }

        this.active = (this.active + step + count) % count;
        this.$nextTick(() => document.getElementById(`palette-option-${this.active}`)?.scrollIntoView({ block: 'nearest' }));
    },

    choose(event) {
        this.select(this.flat[this.active], event);
    },

    select(item, event) {
        if (!item) {
            return;
        }

        if (item.run) {
            event?.preventDefault();
            this.$store.palette.hide();
            item.run();

            return;
        }

        event?.preventDefault();

        // Ctrl / ⌘ / Shift + click opens the page in a new tab and keeps the palette open.
        if (event?.type === 'click' && (event.metaKey || event.ctrlKey || event.shiftKey)) {
            window.open(item.url, '_blank', 'noopener');

            return;
        }

        this.$store.palette.hide();

        if (item.newTab) {
            window.open(item.url, '_blank', 'noopener');
        } else {
            window.location.assign(item.url);
        }
    },

    icon(name) {
        return iconMarkup(name);
    },
}));

window.Alpine = Alpine;
Alpine.start();
