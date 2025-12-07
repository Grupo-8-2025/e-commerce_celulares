#!/bin/bash
# Script de Verificação Rápida - Validação de Admin
# Uso: ./verify_auth.sh (em sistemas Unix/Linux/Mac)
# 
# Este script verifica se a implementação de AuthHandler está completa

echo "🔍 Verificação de Implementação - Validação de Admin"
echo "========================================================"
echo ""

ERRORS=0

# Verificar se AuthHandler.php existe
echo "1. Verificando AuthHandler.php..."
if [ -f "Control/AuthHandler.php" ]; then
    echo "   ✅ AuthHandler.php encontrado"
    
    # Verificar se tem a classe AuthHandler
    if grep -q "class AuthHandler" Control/AuthHandler.php; then
        echo "   ✅ Classe AuthHandler definida"
    else
        echo "   ❌ Classe AuthHandler NÃO encontrada"
        ERRORS=$((ERRORS + 1))
    fi
    
    # Verificar se tem requerAdmin
    if grep -q "public static function requerAdmin" Control/AuthHandler.php; then
        echo "   ✅ Método requerAdmin() implementado"
    else
        echo "   ❌ Método requerAdmin() NÃO encontrado"
        ERRORS=$((ERRORS + 1))
    fi
else
    echo "   ❌ AuthHandler.php NÃO encontrado"
    ERRORS=$((ERRORS + 1))
fi

echo ""
echo "2. Verificando ProdutoViewController.php..."
if grep -q "require_once __DIR__ . '/AuthHandler.php'" Control/ProdutoViewController.php; then
    echo "   ✅ AuthHandler incluído"
else
    echo "   ❌ AuthHandler NÃO incluído"
    ERRORS=$((ERRORS + 1))
fi

if grep -q "AuthHandler::requerAdmin()" Control/ProdutoViewController.php; then
    echo "   ✅ requerAdmin() utilizado"
else
    echo "   ❌ requerAdmin() NÃO utilizado"
    ERRORS=$((ERRORS + 1))
fi

if grep -q "AuthHandler::registrarAcaoAdmin" Control/ProdutoViewController.php; then
    echo "   ✅ Auditoria implementada"
else
    echo "   ❌ Auditoria NÃO implementada"
    ERRORS=$((ERRORS + 1))
fi

echo ""
echo "3. Verificando VendaController.php..."
if grep -q "require_once __DIR__ . '/AuthHandler.php'" Control/VendaController.php; then
    echo "   ✅ AuthHandler incluído"
else
    echo "   ❌ AuthHandler NÃO incluído"
    ERRORS=$((ERRORS + 1))
fi

if grep -q "AuthHandler::requerAdmin()" Control/VendaController.php; then
    echo "   ✅ requerAdmin() utilizado"
else
    echo "   ❌ requerAdmin() NÃO utilizado"
    ERRORS=$((ERRORS + 1))
fi

echo ""
echo "4. Verificando Documentação..."
if [ -f "Control/AUTH_VALIDATION_GUIDE.md" ]; then
    echo "   ✅ AUTH_VALIDATION_GUIDE.md encontrado"
else
    echo "   ⚠️  AUTH_VALIDATION_GUIDE.md NÃO encontrado"
fi

if [ -f "ADMIN_VALIDATION_SUMMARY.md" ]; then
    echo "   ✅ ADMIN_VALIDATION_SUMMARY.md encontrado"
else
    echo "   ⚠️  ADMIN_VALIDATION_SUMMARY.md NÃO encontrado"
fi

echo ""
echo "========================================================"
if [ $ERRORS -eq 0 ]; then
    echo "✅ Tudo em ordem! Implementação completa."
    exit 0
else
    echo "❌ $ERRORS erro(s) encontrado(s). Verifique a implementação."
    exit 1
fi
