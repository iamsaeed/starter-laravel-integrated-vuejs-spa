<template>
  <svg
    :class="classes"
    :width="iconSize"
    :height="iconSize"
    :fill="fill"
    :stroke="stroke"
    viewBox="0 0 24 24"
    :stroke-width="strokeWidth"
    :role="role"
    :aria-label="ariaLabel"
    :aria-hidden="ariaHidden"
    :style="transformStyle ? { transform: transformStyle } : null"
    @click="handleClick"
  >
    <!-- Title for tooltip -->
    <title v-if="title">{{ title }}</title>

    <path
      v-if="resolvedName === 'dashboard'"
      d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"
    />
    <path
      v-else-if="resolvedName === 'analytics'"
      d="M9 11H7v8h2v-8zm4 4h-2v4h2v-4zm4-8h-2v12h2V7z"
    />

    <path
      v-else-if="resolvedName === 'tasks'"
      d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
    />
    <path
      v-else-if="resolvedName === 'reports'"
      d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"
    />
    <path
      v-else-if="resolvedName === 'team'"
      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
    />
    <path
      v-else-if="resolvedName === 'messages'"
      d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"
    />
    <path
      v-else-if="resolvedName === 'chat'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
    />
    <path
      v-else-if="resolvedName === 'profile'"
      d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
    />
    <g v-else-if="name === 'settings'">
      <path d="M10.5 6a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z" />
      <path d="M10.5 12a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z" />
      <path d="M10.5 18a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z" />
      <path d="M4 6h4m4 0h8M4 12h4m4 0h8M4 18h4m4 0h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </g>
    <path
      v-else-if="resolvedName === 'logout'"
      d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"
    />
    <path
      v-else-if="resolvedName === 'menu'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 6h16M4 12h16M4 18h16"
    />
    <path
      v-else-if="resolvedName === 'close'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M6 18L18 6M6 6l12 12"
    />
    <path
      v-else-if="resolvedName === 'search'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
    />
    <path
      v-else-if="resolvedName === 'bell'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
    />
    <path
      v-else-if="resolvedName === 'chevron-down'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M19 9l-7 7-7-7"
    />
    <path
      v-else-if="resolvedName === 'chevron-up'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M5 15l7-7 7 7"
    />
    <path
      v-else-if="resolvedName === 'chevron-right'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9.5 7l5 5-5 5"
    />
    <path
      v-else-if="resolvedName === 'chevron-left'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15.5 7L10.5 12l5 5"
    />
    <path
      v-else-if="resolvedName === 'shield'"
      d="M12 2L2 7v10c0 5.55 3.84 9.74 9 10.66V12h2v15.66c5.16-.92 9-5.11 9-10.66V7l-10-5z"
    />
    <path
      v-else-if="resolvedName === 'users'"
      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
    />
    <path
      v-else-if="resolvedName === 'check-circle'"
      d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
    />
    <path
      v-else-if="resolvedName === 'clipboard'"
      d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"
    />
    <path
      v-else-if="resolvedName === 'refresh'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
    />
    <g v-else-if="name === 'eye'">
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
      />
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
      />
    </g>
    <path
      v-else-if="resolvedName === 'eye-off'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21m-11.12-11.12A4.973 4.973 0 0112 9c2.5 0 4.478 1.943 5.542 3.875"
    />
    <g v-else-if="name === 'loading'">
      <path
        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"
        opacity="0.3"
      />
      <path
        d="M12 2C17.52 2 22 6.48 22 12c0 .55-.45 1-1 1s-1-.45-1-1c0-4.41-3.59-8-8-8s-8 3.59-8 8c0 .55-.45 1-1 1s-1-.45-1-1C2 6.48 6.48 2 12 2z"
      />
    </g>
    <path
      v-else-if="resolvedName === 'check'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M5 13l4 4L19 7"
    />
    <path
      v-else-if="resolvedName === 'mail'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'clock'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
    />
    <path
      v-else-if="resolvedName === 'sun'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
    />
    <path
      v-else-if="resolvedName === 'moon'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
    />
    <path
      v-else-if="resolvedName === 'check-circle-filled'"
      d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
    />
    <path
      v-else-if="resolvedName === 'x-circle'"
      d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"
    />
    <path
      v-else-if="resolvedName === 'alert-circle'"
      d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"
    />
    <path
      v-else-if="resolvedName === 'info-circle'"
      d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"
    />
    <path
      v-else-if="resolvedName === 'edit'"
      d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"
    />
    <path
      v-else-if="resolvedName === 'delete'"
      d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"
    />
    <path
      v-else-if="resolvedName === 'add'"
      d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"
    />
    <path
      v-else-if="resolvedName === 'more-vertical'"
      d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"
    />
    <path
      v-else-if="resolvedName === 'alert-triangle'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
    />
    <path
      v-else-if="resolvedName === 'globe'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
    />
    <path
      v-else-if="resolvedName === 'server'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-10 4h.01M9 16h.01"
    />
    <path
      v-else-if="resolvedName === 'database'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"
    />
    <g v-else-if="name === 'palette'">
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a6 6 0 00-6-6H9"
      />
      <circle cx="7" cy="8" r="1" />
      <circle cx="7" cy="12" r="1" />
      <circle cx="7" cy="16" r="1" />
    </g>
    <path
      v-else-if="resolvedName === 'download'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
    />
    <path
      v-else-if="resolvedName === 'file'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
    />
    <path
      v-else-if="resolvedName === 'folder'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
    />
    <path
      v-else-if="resolvedName === 'upload'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
    />
    <path
      v-else-if="resolvedName === 'save'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8 7H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
    />
    <path
      v-else-if="resolvedName === 'copy'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'link'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
    />
    <path
      v-else-if="resolvedName === 'external-link'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
    />
    <path
      v-else-if="resolvedName === 'home'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
    />
    <path
      v-else-if="resolvedName === 'arrow-left'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M10 19l-7-7m0 0l7-7m-7 7h18"
    />
    <path
      v-else-if="resolvedName === 'arrow-right'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M14 5l7 7m0 0l-7 7m7-7H3"
    />
    <path
      v-else-if="resolvedName === 'arrow-up'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M5 10l7-7m0 0l7 7m-7-7v18"
    />
    <path
      v-else-if="resolvedName === 'arrow-down'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M19 14l-7 7m0 0l-7-7m7 7V3"
    />
    <path
      v-else-if="resolvedName === 'filter'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
    />
    <path
      v-else-if="resolvedName === 'sort'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
    />
    <path
      v-else-if="resolvedName === 'grid'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
    />
    <path
      v-else-if="resolvedName === 'list'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 6h16M4 10h16M4 14h16M4 18h16"
    />
    <path
      v-else-if="resolvedName === 'user'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
    />
    <path
      v-else-if="resolvedName === 'user-plus'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
    />
    <path
      v-else-if="resolvedName === 'user-minus'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"
    />
    <path
      v-else-if="resolvedName === 'user-check'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
    />
    <path
      v-else-if="resolvedName === 'lock'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
    />
    <path
      v-else-if="resolvedName === 'unlock'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'key'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
    />
    <path
      v-else-if="resolvedName === 'help-circle'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
    />
    <path
      v-else-if="resolvedName === 'question'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
    />
    <path
      v-else-if="resolvedName === 'calendar'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'image'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'video'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'rotate-left'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 12a9 9 0 019-9 9.75 9.75 0 016.74 2.74L21 8M3 12v4m0-4h4m14 0a9 9 0 01-9 9 9.75 9.75 0 01-6.74-2.74L3 16"
    />
    <path
      v-else-if="resolvedName === 'rotate-right'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M21 12a9 9 0 01-9 9 9.75 9.75 0 01-6.74-2.74L3 16m18-4a9 9 0 00-9-9 9.75 9.75 0 00-6.74 2.74L3 8m18 4v4m0-4h-4"
    />
    <path
      v-else-if="resolvedName === 'flip-horizontal'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8 3v3a2 2 0 01-2 2H3m18 0h-3a2 2 0 01-2-2V3m-4 18h8a2 2 0 002-2v-6a2 2 0 00-2-2h-8a2 2 0 00-2 2v6a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'flip-vertical'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 8h3a2 2 0 012 2v3m0 6v-3a2 2 0 012-2h3M21 12H9a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2v-6a2 2 0 00-2-2z"
    />
    <path
      v-else-if="resolvedName === 'zoom-in'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"
    />
    <path
      v-else-if="resolvedName === 'zoom-out'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"
    />
    <path
      v-else-if="resolvedName === 'layout'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3z"
    />
    <path
      v-else-if="resolvedName === 'layout-sidebar'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 3H5a2 2 0 00-2 2v14a2 2 0 002 2h4M9 3h10a2 2 0 012 2v14a2 2 0 01-2 2H9M9 3v18"
    />
    <path
      v-else-if="resolvedName === 'layout-navbar'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 9V5a2 2 0 012-2h14a2 2 0 012 2v4M3 9v10a2 2 0 002 2h14a2 2 0 002-2V9M3 9h18"
    />
    <path
      v-else-if="resolvedName === 'layout-sidebar-right'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M15 3H5a2 2 0 00-2 2v14a2 2 0 002 2h10M15 3v18"
    />
    <path
      v-else-if="resolvedName === 'layout-sidebar-left'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 3H5a2 2 0 00-2 2v14a2 2 0 002 2h4M9 3h10a2 2 0 012 2v14a2 2 0 01-2 2H9M9 3v18"
    />
    <path
      v-else-if="resolvedName === 'cog'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"
    />
    <path
      v-else-if="resolvedName === 'monitor'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'device-mobile'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'document-text'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
    />
    <path
      v-else-if="resolvedName === 'eye-slash'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
    />
    <path
      v-else-if="resolvedName === 'code'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"
    />
    <path
      v-else-if="resolvedName === 'cube-transparent'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5m-2-3.5l-2-1m2 1v-2.5m-2-3.5L4 7"
    />
    <path
      v-else-if="resolvedName === 'shield-exclamation'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016zM12 9v2m0 4h.01"
    />
    <path
      v-else-if="resolvedName === 'x-mark'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M6 18L18 6M6 6l12 12"
    />
    <path
      v-else-if="resolvedName === 'paper-airplane'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
    />
    <path
      v-else-if="resolvedName === 'plus'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 4v16m8-8H4"
    />
    <!-- Rich Text Editor Icons -->
    <path
      v-else-if="resolvedName === 'bold'"
      d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"
    />
    <path
      v-else-if="resolvedName === 'italic'"
      d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"
    />
    <path
      v-else-if="resolvedName === 'underline'"
      d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"
    />
    <path
      v-else-if="resolvedName === 'strikethrough'"
      d="M10 19h4v-3h-4v3zM5 4v3h5v3h4V7h5V4H5zM3 14h18v-2H3v2z"
    />
    <path
      v-else-if="resolvedName === 'list-ordered'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 4h1v6H3zm1 9a1 1 0 110-2 1 1 0 010 2zm0 4a1 1 0 110-2 1 1 0 010 2zm5-13h13M9 12h13M9 20h13"
    />
    <path
      v-else-if="resolvedName === 'list-checks'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
    />
    <path
      v-else-if="resolvedName === 'code-2'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'align-left'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 6h16M4 12h10M4 18h16"
    />
    <path
      v-else-if="resolvedName === 'align-center'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 6h16M7 12h10M4 18h16"
    />
    <path
      v-else-if="resolvedName === 'align-right'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 6h16M10 12h10M4 18h16"
    />
    <path
      v-else-if="resolvedName === 'quote'"
      d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"
    />
    <path
      v-else-if="resolvedName === 'undo'"
      d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"
    />
    <path
      v-else-if="resolvedName === 'redo'"
      d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z"
    />
    <path
      v-else-if="resolvedName === 'eraser'"
      d="M16.24 3.56l4.95 4.94c.78.79.78 2.05 0 2.84L12 20.53a4.008 4.008 0 01-5.66 0L2.81 17c-.78-.79-.78-2.05 0-2.84l10.6-10.6c.79-.78 2.05-.78 2.83 0M4.22 15.58l3.54 3.53c.78.79 2.04.79 2.83 0l3.53-3.53-4.95-4.95-4.95 4.95z"
    />
    <path
      v-else-if="resolvedName === 'maximize'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
    />
    <path
      v-else-if="resolvedName === 'minimize'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"
    />
    <path
      v-else-if="resolvedName === 'circle'"
      d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"
    />
    <path
      v-else-if="resolvedName === 'pencil'"
      d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"
    />
    <path
      v-else-if="resolvedName === 'trash'"
      d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"
    />
    <!-- Phase 1: Critical Missing Icons (15 icons) -->
    <!-- Status & Indicators -->
    <path
      v-else-if="resolvedName === 'star'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
    />
    <path
      v-else-if="resolvedName === 'star-filled'"
      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
    />
    <path
      v-else-if="resolvedName === 'bookmark'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
    />
    <path
      v-else-if="resolvedName === 'bookmark-filled'"
      d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
    />
    <path
      v-else-if="resolvedName === 'flag'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 21v-6m0 0V5a2 2 0 012-2h9.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V15l-5-3-5 3z"
    />
    <path
      v-else-if="resolvedName === 'badge-check'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
    />
    <path
      v-else-if="resolvedName === 'heart'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
    />
    <!-- E-commerce -->
    <path
      v-else-if="resolvedName === 'shopping-cart'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
    />
    <path
      v-else-if="resolvedName === 'credit-card'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3v-8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
    />
    <path
      v-else-if="resolvedName === 'tag'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
    />
    <!-- Core Actions -->
    <path
      v-else-if="resolvedName === 'dots-horizontal'"
      d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"
    />
    <path
      v-else-if="resolvedName === 'minus'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M20 12H4"
    />
    <path
      v-else-if="resolvedName === 'share'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
    />
    <path
      v-else-if="resolvedName === 'phone'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
    />
    <!-- Data & Communication -->
    <path
      v-else-if="resolvedName === 'chart-bar'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
    />
    <path
      v-else-if="resolvedName === 'inbox'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
    />
    <!-- Phase 2: High-Value Icons (12 icons) -->
    <!-- Media Controls -->
    <path
      v-else-if="resolvedName === 'play'"
      d="M8 5v14l11-7z"
    />
    <path
      v-else-if="resolvedName === 'pause'"
      d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"
    />
    <!-- E-commerce & Business -->
    <path
      v-else-if="resolvedName === 'currency-dollar'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
    />
    <path
      v-else-if="resolvedName === 'receipt'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"
    />
    <!-- Social Features -->
    <path
      v-else-if="resolvedName === 'at-symbol'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
    />
    <path
      v-else-if="resolvedName === 'hashtag'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"
    />
    <!-- Workflow & Actions -->
    <path
      v-else-if="resolvedName === 'pin'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M5 5a7 7 0 1014 0M12 5v14m0 0l-2-2m2 2l2-2"
    />
    <path
      v-else-if="resolvedName === 'archive'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
    />
    <path
      v-else-if="resolvedName === 'duplicate'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
    />
    <!-- Advanced Charts -->
    <path
      v-else-if="resolvedName === 'chart-line'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
    />
    <path
      v-else-if="resolvedName === 'chart-pie'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"
    />
    <!-- Fullscreen -->
    <path
      v-else-if="resolvedName === 'arrows-pointing-out'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
    />
    <path
      v-else-if="resolvedName === 'arrows-pointing-in'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"
    />
    <!-- Phase 3: Nice-to-Have Icons (13 icons) -->
    <!-- Media Capture -->
    <path
      v-else-if="resolvedName === 'camera'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z"
    />
    <path
      v-else-if="resolvedName === 'microphone'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
    />
    <path
      v-else-if="resolvedName === 'music-note'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
    />
    <path
      v-else-if="resolvedName === 'film'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"
    />
    <!-- Workflow Tools -->
    <path
      v-else-if="resolvedName === 'paperclip'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
    />
    <path
      v-else-if="resolvedName === 'adjustments'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
    />
    <!-- File Management -->
    <path
      v-else-if="resolvedName === 'folder-open'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"
    />
    <path
      v-else-if="resolvedName === 'folder-plus'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 17h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"
    />
    <!-- Social Reactions -->
    <path
      v-else-if="resolvedName === 'thumbs-up'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"
    />
    <path
      v-else-if="resolvedName === 'thumbs-down'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"
    />
    <!-- Data & Business -->
    <path
      v-else-if="resolvedName === 'table'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
    />
    <path
      v-else-if="resolvedName === 'lightning-bolt'"
      d="M13 10V3L4 14h7v7l9-11h-7z"
    />
    <!-- Website Builder Icons -->
    <path
      v-else-if="resolvedName === 'newspaper'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z"
    />
    <path
      v-else-if="resolvedName === 'squares-plus'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H6A2.25 2.25 0 003.75 6v2.25A2.25 2.25 0 006 10.5zm0 9.75h2.25A2.25 2.25 0 0010.5 18v-2.25a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25V18A2.25 2.25 0 006 20.25zm9.75-9.75H18a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0018 3.75h-2.25A2.25 2.25 0 0013.5 6v2.25a2.25 2.25 0 002.25 2.25z"
    />
    <path
      v-else-if="resolvedName === 'bars-3'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
    />
    <path
      v-else-if="resolvedName === 'code-bracket'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"
    />
    <path
      v-else-if="resolvedName === 'cog-6-tooth'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z"
    />
    <path
      v-else-if="resolvedName === 'document-duplicate'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"
    />
    <path
      v-else-if="resolvedName === 'squares-2x2'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"
    />
    <path
      v-else-if="resolvedName === 'arrow-path'"
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"
    />
  </svg>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'Icon',
  props: {
    name: {
      type: String,
      required: true,
    },
    size: {
      type: [String, Number],
      default: 24,
      validator: (value) =>
        typeof value === 'number' ||
        ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value),
    },
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'primary', 'secondary', 'muted'].includes(value),
    },
    fill: {
      type: String,
      default: 'currentColor',
    },
    stroke: {
      type: String,
      default: 'none',
    },
    strokeWidth: {
      type: [String, Number],
      default: 0,
    },
    class: {
      type: [String, Array, Object],
      default: '',
    },
    // Animation props
    animate: {
      type: String,
      default: null,
      validator: (value) => !value || ['spin', 'pulse', 'bounce'].includes(value),
    },
    // Accessibility props
    ariaLabel: {
      type: String,
      default: null,
    },
    ariaHidden: {
      type: Boolean,
      default: false,
    },
    decorative: {
      type: Boolean,
      default: false,
    },
    // Transform props
    rotate: {
      type: Number,
      default: 0,
      validator: (value) => [0, 90, 180, 270].includes(value),
    },
    flipH: {
      type: Boolean,
      default: false,
    },
    flipV: {
      type: Boolean,
      default: false,
    },
    // Interactive props
    clickable: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    // Tooltip
    title: {
      type: String,
      default: null,
    },
  },
  emits: ['click'],
  setup(props, { emit }) {
    // Icon aliases mapping
    const iconAliases = {
      'delete': 'trash',
      'remove': 'trash',
      'add': 'plus',
      'create': 'plus',
      'settings': 'cog',
      'config': 'cog',
      'configure': 'cog',
      'checkmark': 'check',
      'tick': 'check',
      'cross': 'close',
      'times': 'close',
      'cancel': 'x-mark',
      'send': 'paper-airplane',
      'signin': 'login',
      'signout': 'logout',
    }

    // Resolve icon name from aliases
    const resolvedName = computed(() => iconAliases[props.name] || props.name)

    // Size presets
    const iconSize = computed(() => {
      const sizeMap = { xs: 12, sm: 16, md: 20, lg: 24, xl: 32, '2xl': 48 }
      return sizeMap[props.size] || props.size
    })

    // Handle stroke icons
    const isStrokeIcon = computed(() => {
      return [
        'menu', 'close', 'search', 'bell', 'chevron-down', 'chevron-up', 'chevron-right', 'chevron-left',
        'refresh', 'eye', 'eye-off', 'check', 'mail', 'clock', 'sun', 'moon', 'alert-triangle',
        'globe', 'server', 'database', 'download', 'file', 'folder', 'upload', 'save', 'copy',
        'link', 'external-link', 'home', 'arrow-left', 'arrow-right', 'arrow-up', 'arrow-down',
        'filter', 'sort', 'grid', 'list', 'user', 'user-plus', 'user-minus', 'user-check',
        'lock', 'unlock', 'key', 'help-circle', 'question', 'calendar', 'image', 'video',
        'rotate-left', 'rotate-right', 'flip-horizontal', 'flip-vertical', 'zoom-in', 'zoom-out',
        'layout', 'layout-sidebar', 'layout-navbar', 'layout-sidebar-right', 'layout-sidebar-left',
        'cog', 'monitor', 'device-mobile', 'document-text', 'eye-slash', 'code', 'cube-transparent',
        'shield-exclamation', 'x-mark', 'paper-airplane', 'plus', 'list-ordered', 'list-checks',
        'code-2', 'align-left', 'align-center', 'align-right', 'maximize', 'minimize', 'chat',
        'palette',
        // Phase 1 stroke icons
        'star', 'bookmark', 'flag', 'badge-check', 'heart', 'shopping-cart', 'credit-card', 'tag',
        'minus', 'share', 'phone', 'chart-bar', 'inbox',
        // Phase 2 stroke icons
        'currency-dollar', 'receipt', 'at-symbol', 'hashtag', 'pin', 'archive',
        'duplicate', 'chart-line', 'chart-pie', 'arrows-pointing-out', 'arrows-pointing-in',
        // Phase 3 stroke icons
        'camera', 'microphone', 'music-note', 'film', 'paperclip',
        'adjustments', 'folder-open', 'folder-plus', 'thumbs-up', 'thumbs-down', 'table',
        // Website Builder icons
        'newspaper', 'squares-plus', 'bars-3', 'code-bracket', 'cog-6-tooth',
        'document-duplicate', 'squares-2x2', 'arrow-path'
      ].includes(resolvedName.value)
    })

    // Transform style
    const transformStyle = computed(() => {
      const transforms = []

      if (props.rotate) {
        transforms.push(`rotate(${props.rotate}deg)`)
      }

      if (props.flipH) {
        transforms.push('scaleX(-1)')
      }

      if (props.flipV) {
        transforms.push('scaleY(-1)')
      }

      return transforms.length > 0 ? transforms.join(' ') : null
    })

    const classes = computed(() => {
      const baseClasses = ['inline-block', 'flex-shrink-0']

      // Add variant-specific classes
      const variantClasses = {
        default: '',
        primary: 'text-primary-600 dark:text-primary-400',
        secondary: 'text-secondary-600 dark:text-secondary-400',
        muted: 'text-muted',
      }

      // Animation classes
      const animationClasses = {
        spin: 'icon-spin',
        pulse: 'icon-pulse',
        bounce: 'icon-bounce',
      }

      // Interactive classes
      const interactiveClasses = []
      if (props.clickable && !props.disabled) {
        interactiveClasses.push('icon-clickable')
      }
      if (props.disabled) {
        interactiveClasses.push('icon-disabled')
      }

      // Transform classes
      const transformClasses = transformStyle.value ? ['icon-transform'] : []

      return [
        ...baseClasses,
        variantClasses[props.variant],
        props.animate ? animationClasses[props.animate] : null,
        ...interactiveClasses,
        ...transformClasses,
        props.class,
      ].filter(Boolean)
    })

    const fill = computed(() => {
      if (isStrokeIcon.value) return 'none'
      return props.fill
    })

    const stroke = computed(() => {
      if (isStrokeIcon.value) return 'currentColor'
      return props.stroke
    })

    const strokeWidth = computed(() => {
      if (isStrokeIcon.value) return 2
      return props.strokeWidth
    })

    // Accessibility
    const role = computed(() => {
      if (props.decorative) return 'presentation'
      return 'img'
    })

    // Click handler
    const handleClick = (event) => {
      if (!props.disabled && (props.clickable || emit)) {
        emit('click', event)
      }
    }

    return {
      resolvedName,
      iconSize,
      classes,
      fill,
      stroke,
      strokeWidth,
      role,
      transformStyle,
      handleClick,
    }
  },
}
</script>

<style scoped>
/* Animation keyframes */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-25%);
  }
}

/* Animation classes */
.icon-spin {
  animation: spin 1s linear infinite;
}

.icon-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.icon-bounce {
  animation: bounce 1s infinite;
}

/* Interactive classes */
.icon-clickable {
  cursor: pointer;
  opacity: 1;
  transition: opacity 0.2s ease;
}

.icon-clickable:hover {
  opacity: 0.7;
}

.icon-disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

/* Transform class */
.icon-transform {
  transform-origin: center;
}
</style>