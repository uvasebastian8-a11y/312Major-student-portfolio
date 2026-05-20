document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('.search-box input');

    // Simple Search Feedback
    searchInput.addEventListener('keyup', (e) => {
        console.log("Searching for:", e.target.value);
        // Future logic for filtering books will go here
    });

    console.log("Library System Script Loaded.");
});