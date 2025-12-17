import AiContentBuilderButton from './components/AiContentBuilderButton.vue';

// AI sparkles icon SVG (si:ai-line inspired)
const aiIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
    <path d="M12 3v2m0 14v2M5.636 5.636l1.414 1.414m9.9 9.9l1.414 1.414M3 12h2m14 0h2M5.636 18.364l1.414-1.414m9.9-9.9l1.414-1.414"/>
    <circle cx="12" cy="12" r="4"/>
</svg>`;

Statamic.booting(() => {
    // Register the Vue component
    Statamic.$components.register('AiContentBuilderButton', AiContentBuilderButton);

    // Check if AI Content Builder is enabled (API key is set)
    const config = Statamic.$config.get('aiContentBuilder');
    if (!config?.enabled) {
        console.warn('AI Content Builder: OpenAI API key not configured. Button will not be available.');
        return;
    }

    // Register the Bard button
    Statamic.$bard.buttons((buttons, button) => {
        const aiButton = button({
            name: 'ai_content_builder',
            text: __('AI Content Builder'),
            component: 'AiContentBuilderButton',
            html: aiIcon,
        });

        if (aiButton) {
            buttons.push(aiButton);
        }
    });
});
