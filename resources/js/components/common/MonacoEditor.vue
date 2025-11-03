<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed, nextTick } from 'vue'
import loader from '@monaco-editor/loader'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  language: {
    type: String,
    default: 'html'
  },
  theme: {
    type: String,
    default: 'auto' // 'auto', 'vs-dark', 'vs-light'
  },
  height: {
    type: String,
    default: '500px'
  },
  options: {
    type: Object,
    default: () => ({})
  },
  errors: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const editorContainer = ref(null)
const isMounted = ref(false)
let editor = null
let monaco = null
let darkModeObserver = null

// Auto-detect dark mode
const isDark = ref(false)
const currentTheme = computed(() => {
  if (props.theme === 'auto') {
    return isDark.value ? 'vs-dark' : 'vs'
  }
  return props.theme === 'vs-dark' ? 'vs-dark' : 'vs'
})

// Check for dark mode
function checkDarkMode() {
  isDark.value = document.documentElement.classList.contains('dark')
}

async function initializeEditor() {
  try {
    // Poll for container to be ready AND in the DOM (max 2 seconds)
    let attempts = 0
    while ((!editorContainer.value || !editorContainer.value.isConnected) && attempts < 20) {
      await new Promise(resolve => setTimeout(resolve, 100))
      attempts++
    }

    if (!editorContainer.value) {
      console.error('MonacoEditor: Container element not available after polling')
      return
    }

    if (!editorContainer.value.isConnected) {
      console.error('MonacoEditor: Container element not connected to DOM')
      return
    }

    monaco = await loader.init()

    // Register Blade language
    monaco.languages.register({ id: 'blade' })

    // Define Blade syntax highlighting
    monaco.languages.setMonarchTokensProvider('blade', {
      tokenizer: {
        root: [
          // Blade directives - control structures
          [/@(if|elseif|else|endif|unless|endunless|isset|empty|auth|guest|production|env)\b/, 'keyword.control'],

          // Blade directives - loops
          [/@(foreach|endforeach|forelse|empty|endforelse|for|endfor|while|endwhile|break|continue)\b/, 'keyword.control'],

          // Blade directives - conditionals
          [/@(switch|case|default|endswitch)\b/, 'keyword.control'],

          // Blocked directives (show as errors)
          [/@(php|endphp|include|includeIf|includeWhen|includeUnless|includeFirst|extends|section|component)\b/, 'invalid'],

          // Other directives
          [/@[a-zA-Z_]\w*/, 'keyword'],

          // Blade echo statements
          [/\{\{/, 'delimiter.curly', '@bladeEcho'],
          [/\{!!/, 'delimiter.curly', '@bladeRawEcho'],
          [/\{\{--/, 'comment', '@bladeComment'],

          // HTML tags
          [/<(!DOCTYPE)/, 'metatag'],
          [/<\/?([a-zA-Z][\w-]*)/, { token: 'tag', next: '@tag' }],

          // Strings
          [/"([^"\\]|\\.)*$/, 'string.invalid'],
          [/'([^'\\]|\\.)*$/, 'string.invalid'],
          [/"/, 'string', '@string_double'],
          [/'/, 'string', '@string_single'],
        ],

        bladeEcho: [
          [/\}\}/, 'delimiter.curly', '@pop'],
          [/\$\w+/, 'variable'],
          [/->/, 'delimiter'],
          [/\w+/, 'identifier'],
          [/[^}]+/, 'variable']
        ],

        bladeRawEcho: [
          [/!!\}/, 'delimiter.curly', '@pop'],
          [/\$\w+/, 'variable'],
          [/->/, 'delimiter'],
          [/\w+/, 'identifier'],
          [/[^!}]+/, 'variable']
        ],

        bladeComment: [
          [/--\}\}/, 'comment', '@pop'],
          [/./, 'comment']
        ],

        tag: [
          [/[ \t\r\n]+/, ''],
          [/([a-zA-Z][\w-]*)/, 'attribute.name'],
          [/=/, 'delimiter'],
          [/"[^"]*"/, 'attribute.value'],
          [/'[^']*'/, 'attribute.value'],
          [/>/, 'tag', '@pop'],
          [/\/>/, 'tag', '@pop'],
        ],

        string_double: [
          [/[^\\"]+/, 'string'],
          [/\\./, 'string.escape'],
          [/"/, 'string', '@pop']
        ],

        string_single: [
          [/[^\\']+/, 'string'],
          [/\\./, 'string.escape'],
          [/'/, 'string', '@pop']
        ],
      }
    })

    // Register autocomplete for Blade
    monaco.languages.registerCompletionItemProvider('blade', {
      provideCompletionItems: (model, position) => {
        const word = model.getWordUntilPosition(position)
        const range = {
          startLineNumber: position.lineNumber,
          endLineNumber: position.lineNumber,
          startColumn: word.startColumn,
          endColumn: word.endColumn
        }

        const suggestions = [
          // Control structures
          {
            label: '@if',
            kind: monaco.languages.CompletionItemKind.Snippet,
            insertText: '@if(${1:condition})\n\t${2}\n@endif',
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Blade if statement',
            range: range
          },
          {
            label: '@foreach',
            kind: monaco.languages.CompletionItemKind.Snippet,
            insertText: '@foreach(${1:\\$items} as ${2:\\$item})\n\t${3}\n@endforeach',
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Blade foreach loop',
            range: range
          },
          {
            label: '@forelse',
            kind: monaco.languages.CompletionItemKind.Snippet,
            insertText: '@forelse(${1:\\$items} as ${2:\\$item})\n\t${3}\n@empty\n\t${4:No items}\n@endforelse',
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Blade forelse loop with empty state',
            range: range
          },
          {
            label: '@unless',
            kind: monaco.languages.CompletionItemKind.Snippet,
            insertText: '@unless(${1:condition})\n\t${2}\n@endunless',
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Blade unless statement',
            range: range
          },
          {
            label: '@isset',
            kind: monaco.languages.CompletionItemKind.Snippet,
            insertText: '@isset(${1:\\$variable})\n\t${2}\n@endisset',
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Blade isset check',
            range: range
          },
          {
            label: '@empty',
            kind: monaco.languages.CompletionItemKind.Snippet,
            insertText: '@empty(${1:\\$variable})\n\t${2}\n@endempty',
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Blade empty check',
            range: range
          },
          {
            label: '{{ }}',
            kind: monaco.languages.CompletionItemKind.Snippet,
            insertText: '{{ ${1:\\$variable} }}',
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Blade echo (escaped)',
            range: range
          },
          {
            label: '{!! !!}',
            kind: monaco.languages.CompletionItemKind.Snippet,
            insertText: '{!! ${1:\\$html} !!}',
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Blade raw echo (unescaped)',
            range: range
          },
          {
            label: '@switch',
            kind: monaco.languages.CompletionItemKind.Snippet,
            insertText: '@switch(${1:\\$variable})\n\t@case(${2:value1})\n\t\t${3}\n\t\t@break\n\t@case(${4:value2})\n\t\t${5}\n\t\t@break\n\t@default\n\t\t${6}\n@endswitch',
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Blade switch statement',
            range: range
          },
          // Helper functions
          {
            label: "config('app.name')",
            kind: monaco.languages.CompletionItemKind.Function,
            insertText: "config('${1:app.name}')",
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Get configuration value',
            range: range
          },
          {
            label: "route('name')",
            kind: monaco.languages.CompletionItemKind.Function,
            insertText: "route('${1:route.name}')",
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Generate URL for named route',
            range: range
          },
          {
            label: "url('path')",
            kind: monaco.languages.CompletionItemKind.Function,
            insertText: "url('${1:path}')",
            insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
            documentation: 'Generate URL',
            range: range
          },
        ]

        return { suggestions }
      }
    })

    // Create editor instance
    editor = monaco.editor.create(editorContainer.value, {
      value: props.modelValue || '',
      language: props.language === 'blade' ? 'blade' : props.language,
      theme: currentTheme.value,
      automaticLayout: true,
      minimap: { enabled: true },
      fontSize: 14,
      lineNumbers: 'on',
      wordWrap: 'on',
      scrollBeyondLastLine: false,
      folding: true,
      suggest: {
        snippetsPreventQuickSuggestions: false,
        showSnippets: true,
      },
      quickSuggestions: {
        other: true,
        comments: false,
        strings: true
      },
      ...props.options
    })

    // Listen for content changes
    editor.onDidChangeModelContent(() => {
      const value = editor.getValue()
      emit('update:modelValue', value)
      emit('change', value)
    })
  } catch (error) {
    console.error('Failed to initialize Monaco Editor:', error)
  }
}

onMounted(() => {
  isMounted.value = true

  checkDarkMode()

  // Watch for dark mode changes
  darkModeObserver = new MutationObserver(checkDarkMode)
  darkModeObserver.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
  })

  // Initialize editor (polling happens inside the function)
  initializeEditor()
})

// Watch for theme changes
watch(currentTheme, (newTheme) => {
  if (editor && monaco) {
    monaco.editor.setTheme(newTheme)
  }
})

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  if (editor && newValue !== editor.getValue()) {
    editor.setValue(newValue || '')
  }
})

// Watch for errors and display them
watch(() => props.errors, (newErrors) => {
  if (!editor || !monaco) return

  const model = editor.getModel()
  if (!model) return

  const markers = newErrors.map(error => ({
    severity: monaco.MarkerSeverity.Error,
    startLineNumber: error.line || 1,
    startColumn: error.column || 1,
    endLineNumber: error.line || 1,
    endColumn: error.column ? error.column + 10 : 100,
    message: error.message || 'Error'
  }))

  monaco.editor.setModelMarkers(model, 'blade-validation', markers)
}, { deep: true })

// Cleanup
onBeforeUnmount(() => {
  isMounted.value = false
  if (editor) {
    editor.dispose()
  }
  if (darkModeObserver) {
    darkModeObserver.disconnect()
  }
})

// Expose editor instance and methods for parent components
defineExpose({
  getEditor: () => editor,
  insertText: (text, offset = 0) => {
    if (editor) {
      const position = editor.getPosition()
      const newPosition = {
        lineNumber: position.lineNumber,
        column: position.column + offset
      }

      editor.executeEdits('', [{
        range: new monaco.Range(
          newPosition.lineNumber,
          newPosition.column,
          newPosition.lineNumber,
          newPosition.column
        ),
        text: text
      }])

      // Focus editor and move cursor
      editor.focus()
      editor.setPosition({
        lineNumber: newPosition.lineNumber,
        column: newPosition.column + text.length
      })
    }
  },
  focus: () => {
    if (editor) {
      editor.focus()
    }
  }
})
</script>

<template>
  <div
    ref="editorContainer"
    :style="{ height }"
    class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden shadow-sm"
  ></div>
</template>
