<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "BAKAYUH API Documentation",
    description: "Dokumentasi Resmi REST API BAKAYUH — Sistem Terpadu Akuntabilitas Kinerja (E-Performance) dan Reformasi Birokrasi (E-RB) Kantor Wilayah Kementerian Hukum Kalimantan Selatan."
)]
#[OA\Server(url: "http://127.0.0.1:8000/api", description: "Local Development Server")]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Sanctum Token",
    description: "Gunakan token Sanctum dari endpoint /auth/login"
)]
abstract class Controller
{
    //
}
