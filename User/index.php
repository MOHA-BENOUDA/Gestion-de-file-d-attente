<?php
class Point {
    private $abs;
    private $ord;

    // Constructeur
    public function __construct($abs = 0, $ord = 0) {
        $this->abs = $abs;
        $this->ord = $ord;
    }

    // Méthode pour calculer la norme
    public function calculeNorme() {
        return sqrt($this->abs * $this->abs + $this->ord * $this->ord);
    }

    // Getter pour abs
    public function getAbs() {
        return $this->abs;
    }

    // Getter pour ord
    public function getOrd() {
        return $this->ord;
    }
}

// Création d'un objet Point avec (3,4)
$p = new Point(3, 4);
echo "La norme du point est : " . $p->calculeNorme();
?>
