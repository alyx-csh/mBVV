QUANDO FOR FAZER AS COISAS PRA BATALHA COLOCAR ESSA FUNÇÃO
    public function indexBatalha() {
    $timeModel = new TimeModel($this->db);
    $quantidadeTimes = $timeModel->contarTimesDoUsuario($_SESSION['idUsuario']);

    // Se não tiver nenhum time, dispara a exceção customizada!
    if ($quantidadeTimes === 0) {
        throw new SemTimeException();
    }

    // Caso contrário, carrega a view normalmente
    require_once __DIR__ . '/../View/batalha/arena.php';
}
