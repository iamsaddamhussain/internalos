import { ref, onMounted, watch } from 'vue';
import localforage from 'localforage';

const theme = ref('dark');

export function useTheme() {
    const setTheme = async (newTheme) => {
        theme.value = newTheme;
        await localforage.setItem('theme', newTheme);
        
        if (newTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    const toggleTheme = async () => {
        const newTheme = theme.value === 'dark' ? 'light' : 'dark';
        await setTheme(newTheme);
    };

    const initTheme = async () => {
        const savedTheme = await localforage.getItem('theme');
        if (savedTheme) {
            theme.value = savedTheme;
        } else {
            // Default to dark theme
            theme.value = 'dark';
        }
        
        // Apply theme to document
        if (theme.value === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    return {
        theme,
        setTheme,
        toggleTheme,
        initTheme,
    };
}
