document.addEventListener('DOMContentLoaded', function() {
    const typeCompte = document.getElementById('type_compte');
    const classeGroup = document.getElementById('classeGroup');
    const classeSelect = document.getElementById('classe');

    typeCompte.addEventListener('change', function() {
        if (this.value === 'admin') {
            classeGroup.style.display = 'none';
            classeSelect.removeAttribute('required');
        } else if (this.value === 'parent' || this.value === 'eleve') {
            classeGroup.style.display = 'block';
            classeSelect.setAttribute('required', 'required');
        } else {
            classeGroup.style.display = 'none';
            classeSelect.removeAttribute('required');
        }
    });
});
