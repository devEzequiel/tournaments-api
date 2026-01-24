export function useToast() {
    const showToast = (message, type = 'info') => {
        const event = new CustomEvent('show-toast', {
            detail: { message, type }
        });
        window.dispatchEvent(event);
    };

    return {
        toast: showToast,
        success: (message) => showToast(message, 'success'),
        error: (message) => showToast(message, 'error'),
        warning: (message) => showToast(message, 'warning'),
        info: (message) => showToast(message, 'info'),
    };
}
