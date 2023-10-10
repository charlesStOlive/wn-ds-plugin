$(document).ready(function() {
    $('.mi_label, .mi_value').each(function() {
        var $this = $(this);

        // Vérifie si le texte est tronqué
        if ($this[0].scrollWidth > $this.innerWidth()) {
            $this.attr('title', $this.text());
        }
    });
});
