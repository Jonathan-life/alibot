<?php
// Este archivo actúa como endpoint de la API

require_once __DIR__ . '/../Controllers/RegistroController.php';

// Instanciar el controlador
$controller = new RegistroController();

// Ejecutar método registrar
$controller->registrar();
