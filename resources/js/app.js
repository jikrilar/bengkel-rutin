document.addEventListener('livewire:init', () => {
    window.dispatchEvent(new CustomEvent('application-ready'));
});
