#!/bin/bash

echo "=== FlexOffice - Vérification des Fixtures ==="
echo ""

echo "📊 Statistiques générales :"
symfony console doctrine:query:sql "SELECT 'Utilisateurs' as type, COUNT(*) as total FROM user UNION SELECT 'Espaces', COUNT(*) FROM space UNION SELECT 'Bureaux', COUNT(*) FROM desk UNION SELECT 'Adresses', COUNT(*) FROM address UNION SELECT 'Équipements', COUNT(*) FROM equipment UNION SELECT 'Réservations', COUNT(*) FROM reservation UNION SELECT 'Disponibilités', COUNT(*) FROM availability"

echo ""
echo "👥 Comptes utilisateurs créés :"
symfony console doctrine:query:sql "SELECT email, firstname, lastname, roles FROM user ORDER BY id"

echo ""
echo "🏢 Espaces créés :"
symfony console doctrine:query:sql "SELECT s.name, a.city FROM space s LEFT JOIN address a ON s.address_id = a.id ORDER BY s.id"

echo ""
echo "✅ Fixtures chargées avec succès !"
echo "🔑 Mot de passe pour tous les comptes : 12345678"
echo ""
echo "🌐 Comptes de test :"
echo "   - Admin : admin@flexoffice.com"
echo "   - Host 1 : host@flexoffice.com"
echo "   - Host 2 : host2@flexoffice.com"
echo "   - Guest 1 : guest@flexoffice.com"
echo "   - Guest 2 : guest2@flexoffice.com"
echo "   - Guest 3 : guest3@flexoffice.com"
