import { defineConfig, presetUno, presetAttributify, presetIcons } from 'unocss'

export default defineConfig({
  presets: [
    presetUno(),
    presetAttributify(),
    presetIcons(),
  ],
  theme: {
    colors: {
      primary: {
        50: '#eef2ff',
        100: '#e0e7ff',
        200: '#c7d2fe',
        300: '#a5b4fc',
        400: '#818cf8',
        500: '#6366f1',
        600: '#4f46e5',
        700: '#4338ca',
        800: '#3730a3',
        900: '#312e81',
        950: '#1e1b4b',
      }
    }
  },
  shortcuts: {
    // 自定义快捷方式
    'btn-primary': 'bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors duration-200',
    'btn-primary-outline': 'border border-primary-500 text-primary-500 hover:bg-primary-50 px-4 py-2 rounded-lg transition-colors duration-200',
    'text-primary': 'text-primary-500',
    'bg-primary': 'bg-primary-500',
    'border-primary': 'border-primary-500',
  }
}) 