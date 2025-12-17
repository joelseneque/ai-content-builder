<template>
    <popover ref="popover" placement="bottom-start" @closed="handleClose" :clickaway="false">
        <template #trigger>
            <button
                class="bard-toolbar-button"
                :class="{ active: modalActive }"
                v-tooltip="button.text"
                :aria-label="button.text"
                @click="toggleModal"
            >
                <div class="flex items-center" v-html="button.html"></div>
            </button>
        </template>
        <template #default>
            <div v-if="modalActive" class="ai-content-builder-modal p-4" style="width: 400px; max-width: 90vw;">
                <h3 class="text-lg font-semibold mb-3">{{ __('AI Content Builder') }}</h3>

                <!-- Instructions -->
                <div v-if="instructions" class="mb-4 p-3 bg-gray-100 dark:bg-dark-700 rounded-md text-sm">
                    <p class="text-gray-700 dark:text-dark-150 whitespace-pre-wrap">{{ instructions }}</p>
                </div>

                <!-- User Input -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">
                        {{ __('What content would you like to generate?') }}
                    </label>
                    <textarea
                        ref="promptInput"
                        v-model="userPrompt"
                        class="input-text w-full"
                        style="min-height: 120px;"
                        :placeholder="__('Describe the content you want to create...')"
                        :disabled="loading"
                        @keydown.meta.enter="generateContent"
                        @keydown.ctrl.enter="generateContent"
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ __('Press Cmd+Enter (Mac) or Ctrl+Enter (Win) to generate') }}
                    </p>
                </div>

                <!-- Word Limit Info -->
                <div v-if="wordLimit > 0" class="mb-4 text-xs text-gray-500">
                    {{ __('Word limit:') }} {{ wordLimit }} {{ __('words') }}
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mb-4 p-3 bg-red-100 dark:bg-red-900/30 rounded-md text-sm text-red-700 dark:text-red-300">
                    {{ error }}
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        class="btn"
                        @click="closeModal"
                        :disabled="loading"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="button"
                        class="btn-primary"
                        @click="generateContent"
                        :disabled="loading || !userPrompt.trim()"
                    >
                        <span v-if="loading" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('Generating...') }}
                        </span>
                        <span v-else>{{ __('Generate Content') }}</span>
                    </button>
                </div>
            </div>
        </template>
    </popover>
</template>

<script>
import { marked } from 'marked';

export default {
    mixins: [BardToolbarButton],

    data() {
        return {
            modalActive: false,
            userPrompt: '',
            loading: false,
            error: null,
        };
    },

    computed: {
        instructions() {
            return this.config.ai_instructions || null;
        },
        systemPrompt() {
            return this.config.ai_prompt || null;
        },
        wordLimit() {
            return parseInt(this.config.ai_word_limit) || 250;
        },
    },

    methods: {
        toggleModal() {
            this.modalActive = !this.modalActive;
            if (this.modalActive) {
                this.$nextTick(() => {
                    if (this.$refs.promptInput) {
                        this.$refs.promptInput.focus();
                    }
                });
            } else {
                this.resetState();
                this.editor.commands.focus();
            }
        },

        handleClose() {
            if (this.modalActive && !this.loading) {
                this.closeModal();
            }
        },

        closeModal() {
            if (this.loading) return;
            this.modalActive = false;
            this.$refs.popover.close();
            this.resetState();
            this.editor.commands.focus();
        },

        resetState() {
            this.userPrompt = '';
            this.error = null;
            this.loading = false;
        },

        async generateContent() {
            if (!this.userPrompt.trim() || this.loading) return;

            this.loading = true;
            this.error = null;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const response = await fetch('/cp/pqd/ai-content-builder/ai-content/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        prompt: this.userPrompt,
                        system_prompt: this.systemPrompt,
                        word_limit: this.wordLimit,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Failed to generate content');
                }

                if (data.content) {
                    this.insertContent(data.content);
                    this.closeModal();
                } else {
                    throw new Error('No content received from API');
                }
            } catch (err) {
                console.error('AI Content Builder error:', err);
                this.error = err.message || 'An error occurred while generating content';
            } finally {
                this.loading = false;
            }
        },

        insertContent(markdownContent) {
            // Configure marked for safe HTML output
            marked.setOptions({
                breaks: true,
                gfm: true,
            });

            // Convert markdown to HTML
            const html = marked.parse(markdownContent);

            // Insert the HTML content into the Bard editor
            this.editor.chain().focus().insertContent(html).run();
        },
    },
};
</script>

<style scoped>
.ai-content-builder-modal {
    max-height: 80vh;
    overflow-y: auto;
}
</style>
