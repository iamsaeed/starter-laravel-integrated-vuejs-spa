export const theme = {
  colors: {
    primary: {
      50: '#f5f3ff',
      100: '#ede9fe',
      200: '#ddd6fe',
      300: '#c4b5fd',
      400: '#a78bfa',
      500: '#8b5cf6',
      600: '#7c3aed',
      700: '#6d28d9',
      800: '#5b21b6',
      900: '#4c1d95',
    },
    secondary: {
      50: '#eff6ff',
      100: '#dbeafe',
      200: '#bfdbfe',
      300: '#93c5fd',
      400: '#60a5fa',
      500: '#3b82f6',
      600: '#2563eb',
      700: '#1d4ed8',
      800: '#1e40af',
      900: '#1e3a8a',
    },
    success: {
      50: '#f0fdf4',
      100: '#dcfce7',
      200: '#bbf7d0',
      300: '#86efac',
      400: '#4ade80',
      500: '#22c55e',
      600: '#16a34a',
      700: '#15803d',
      800: '#166534',
      900: '#14532d',
    },
    warning: {
      50: '#fffbeb',
      100: '#fef3c7',
      200: '#fde68a',
      300: '#fcd34d',
      400: '#fbbf24',
      500: '#f59e0b',
      600: '#d97706',
      700: '#b45309',
      800: '#92400e',
      900: '#78350f',
    },
    danger: {
      50: '#fef2f2',
      100: '#fee2e2',
      200: '#fecaca',
      300: '#fca5a5',
      400: '#f87171',
      500: '#ef4444',
      600: '#dc2626',
      700: '#b91c1c',
      800: '#991b1b',
      900: '#7f1d1d',
    },
  },
  gradients: {
    sidebar: 'from-primary-600 to-secondary-600',
    sidebarDark: 'dark:from-primary-800 dark:to-secondary-800',
    statBlue: 'from-secondary-500 to-secondary-600',
    statGreen: 'from-success-500 to-success-600',
    statPurple: 'from-primary-500 to-primary-600',
    statOrange: 'from-warning-500 to-warning-600',
    iconBg: 'from-primary-500 to-secondary-500',
  },
  spacing: {
    sidebar: {
      width: '16rem', // 64 / 4
      collapsedWidth: '4rem', // 16 / 4
    },
    navbar: {
      height: '4rem', // 16 / 4
    },
  },
  borderRadius: {
    card: '0.75rem', // 12px
    button: '0.5rem', // 8px
    input: '0.5rem', // 8px
  },
  shadows: {
    card: '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
    sidebar: '0 25px 50px -12px rgb(0 0 0 / 0.25)',
    dropdown: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
  },
  transitions: {
    default: 'all 0.2s ease-in-out',
    sidebar: 'all 0.3s ease-in-out',
    colors: 'colors 0.3s ease-in-out',
  },
}

export const themeClasses = {
  // Sidebar styles
  sidebar: {
    base: 'fixed inset-y-0 left-0 z-50 bg-gradient-to-b from-primary-600 to-secondary-600 dark:from-primary-800 dark:to-secondary-800 transform transition-all duration-300 ease-in-out shadow-xl',
    logo: 'w-8 h-8 bg-white dark:bg-gray-100 rounded-lg flex items-center justify-center shadow-md flex-shrink-0',
    logoIcon: 'w-5 h-5 text-primary-600 dark:text-primary-700',
    logoText: 'text-white transition-opacity duration-300',
    logoTitle: 'text-lg font-bold leading-tight',
    logoSubtitle: 'text-xs text-primary-200 dark:text-primary-300',
  },

  // Navigation styles
  nav: {
    sectionTitle: 'text-xs font-semibold text-primary-200 dark:text-primary-300 uppercase tracking-wider',
    itemActive: 'bg-white/20 text-white shadow-md',
    itemInactive: 'text-primary-200 dark:text-primary-300 hover:text-white hover:bg-white/10',
    itemBase: 'flex items-center text-sm font-medium rounded-lg transition-all duration-200 group relative',
  },

  // Card styles
  card: {
    base: 'bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-colors duration-300',
    stat: 'rounded-lg p-6 text-white',
    content: 'bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6',
  },

  // Button styles
  button: {
    primary: 'bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-all duration-200',
    secondary: 'bg-secondary-600 hover:bg-secondary-700 text-white font-medium rounded-lg transition-all duration-200',
    ghost: 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 transition-all duration-200',
  },

  // Text styles
  text: {
    title: 'text-gray-900 dark:text-white',
    subtitle: 'text-gray-600 dark:text-gray-300',
    muted: 'text-gray-500 dark:text-gray-400',
    accent: 'text-primary-600 dark:text-primary-400',
  },
}