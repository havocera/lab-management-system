import { defineConfig, presetUno, presetAttributify, presetIcons } from 'unocss'
import carbon from '@iconify-json/carbon'
import iconCarbon from '@iconify-json/carbon/icons.json'
function generateIconClasses(iconCollections) {
  const iconClasses = []
  
  Object.entries(iconCollections).forEach(([collectionName, collection]) => {
    if (collection.icons) {
      Object.keys(collection.icons).forEach(iconName => {
        iconClasses.push(`i-carbon-${iconName}`)
      })
    }
  })
  
  return iconClasses
}

const iconCollections = {
  iconCarbon
}

const allIconClasses = generateIconClasses(iconCollections)
export default defineConfig({
  presets: [
    presetUno(),
    presetAttributify(),
    presetIcons({
      collections: {
        carbon,
      },
      extraProperties: {
        'display': 'inline-block',
        'vertical-align': 'middle',
      },
      scale: 1.2,
      warn: true,
    }),
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
  },
  safelist:[
    'i-carbon-refresh',
     'i-carbon-add',
    'i-carbon-search',
    'i-carbon-reset',
    'i-carbon-edit',
    'i-carbon-delete',
    'i-carbon-view',
    'i-carbon-dashboard',
    'i-carbon-home',
    'i-carbon-menu',
    'i-carbon-user',
    'i-carbon-settings',
    'i-carbon-account',
    'i-carbon-assembly'
  ]
}) 