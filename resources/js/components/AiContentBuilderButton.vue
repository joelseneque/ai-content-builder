<template>
    <Popover ref="popoverRef" align="start" :inset="true" class="w-[420px]!">
        <template #trigger>
            <Button
                variant="ghost"
                size="sm"
                :aria-label="button.text"
            >
                <div class="flex items-center" v-html="button.html"></div>
            </Button>
        </template>

        <div class="ai-content-builder-modal w-[400px] max-w-[90vw] p-4">
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

            <!-- AI Response Word Count -->
            <div v-if="maxWordLimit > 0" class="mb-4">
                <label class="block text-sm font-medium mb-2">
                    {{ __('AI Response Word Count') }}
                </label>
                <input
                    type="number"
                    v-model.number="requestedWordCount"
                    class="input-text w-24"
                    :min="1"
                    :max="maxWordLimit"
                    :disabled="loading"
                />
                <span class="text-xs text-gray-500 ml-2">{{ __('max') }} {{ maxWordLimit }}</span>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="mb-4 p-3 bg-red-100 dark:bg-red-900/30 rounded-md text-sm text-red-700 dark:text-red-300">
                {{ error }}
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2">
                <Button
                    :text="__('Cancel')"
                    @click="closePopover"
                    :disabled="loading"
                />
                <Button
                    icon="ai-spark"
                    :text="loading ? __('Generating...') : __('Generate Content')"
                    variant="primary"
                    :loading="loading"
                    @click="generateContent"
                    :disabled="loading || !userPrompt.trim()"
                />
            </div>
        </div>
    </Popover>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Button, Popover } from '@statamic/cms/ui';
import { marked } from 'marked';

const props = defineProps({
    button: Object,
    active: Boolean,
    variant: String,
    config: Object,
    bard: {},
    editor: {},
});

const popoverRef = ref(null);
const userPrompt = ref('');
const requestedWordCount = ref(100);
const loading = ref(false);
const error = ref(null);

const instructions = computed(() => props.config?.ai_instructions || null);
const systemPrompt = computed(() => props.config?.ai_prompt || null);
const maxWordLimit = computed(() => parseInt(props.config?.ai_word_limit) || 250);
const effectiveWordCount = computed(() => {
    if (maxWordLimit.value > 0 && requestedWordCount.value > maxWordLimit.value) {
        return maxWordLimit.value;
    }
    return requestedWordCount.value || 100;
});

function resetState() {
    userPrompt.value = '';
    requestedWordCount.value = 100;
    error.value = null;
    loading.value = false;
}

function closePopover() {
    if (loading.value) return;
    popoverRef.value?.close?.();
    resetState();
    props.editor?.commands?.focus();
}

async function generateContent() {
    if (!userPrompt.value.trim() || loading.value) return;

    loading.value = true;
    error.value = null;

    try {
        const csrfToken = Statamic.$config.get('csrfToken');
        if (!csrfToken) {
            throw new Error('CSRF token not found');
        }

        const response = await fetch('/cp/ai-content/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                prompt: userPrompt.value,
                system_prompt: systemPrompt.value,
                word_limit: effectiveWordCount.value,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to generate content');
        }

        if (data.content) {
            insertContent(data.content);
            closePopover();
        } else {
            throw new Error('No content received from API');
        }
    } catch (err) {
        console.error('AI Content Builder error:', err);
        error.value = err.message || 'An error occurred while generating content';
    } finally {
        loading.value = false;
    }
}

function insertContent(markdownContent) {
    marked.setOptions({
        breaks: true,
        gfm: true,
    });

    const html = marked.parse(markdownContent);
    props.editor.chain().focus().insertContent(html).run();
}
</script>

<style scoped>
.ai-content-builder-modal {
    max-height: 80vh;
    overflow-y: auto;
}
</style>
