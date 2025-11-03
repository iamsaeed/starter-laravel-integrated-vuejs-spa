import { useContextRoutes } from '@/composables/useContextRoutes'

// Admin menu items
export const adminMainMenuItems = [
  { to: { name: 'admin.dashboard' }, icon: 'dashboard', label: 'Dashboard' },
  { to: { name: 'admin.users' }, icon: 'team', label: 'Users' },
  { to: { name: 'admin.roles' }, icon: 'shield', label: 'Roles' },
  { to: { name: 'admin.countries' }, icon: 'globe', label: 'Countries' },
  { to: { name: 'admin.timezones' }, icon: 'clock', label: 'Timezones' },
  { to: { name: 'admin.email-templates.index' }, icon: 'mail', label: 'Email Templates' },
]

export function getAdminMoreMenuItems() {
  const { profileRoutes, settingsRoutes } = useContextRoutes()
  return [
    { to: { name: profileRoutes.value.personal }, icon: 'profile', label: 'Profile' },
    { to: { name: settingsRoutes.value.appearance }, icon: 'settings', label: 'Settings' },
  ]
}

// User menu items
export const userMainMenuItems = [
  { to: { name: 'user.dashboard' }, icon: 'dashboard', label: 'Dashboard', exactMatch: true },
]

export function getUserMoreMenuItems() {
  const { profileRoutes, settingsRoutes } = useContextRoutes()
  return [
    { to: { name: profileRoutes.value.personal }, icon: 'profile', label: 'Profile' },
    { to: { name: settingsRoutes.value.appearance }, icon: 'settings', label: 'Settings' },
  ]
}