#!/bin/bash
set -e

# Desactivar módulos conflictivos
a2dismod mpm_event 2>/dev/null || true
a2dismod mpm_worker 2>/dev/null || true
a2dismod mpm_prefork 2>/dev/null || true

# Activar únicamente el que necesita PHP
a2enmod mpm_prefork

# Iniciar Apache normalmente
exec apache2-foreground
