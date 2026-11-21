<?php
/**
 * Test script for Signalements (Reporting) System
 */
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/Models/Moderation/Signalement.php';
require_once ROOT_PATH . '/app/Services/ModerationService.php';
require_once ROOT_PATH . '/app/Models/Moderation/RoleAccess.php';

global $pdo;
if (!($pdo instanceof PDO)) {
    die("Database connection failed.\n");
}

echo "=== DÉBUT DES TESTS SIGNALEMENTS ===\n";

// 1. Fetch test users
$admin = $pdo->query("SELECT id, prenom, nom, role FROM utilisateurs WHERE role = 'administrateur' AND est_actif = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$students = $pdo->query("SELECT id, prenom, nom, role FROM utilisateurs WHERE role = 'etudiant' AND est_actif = 1 ORDER BY id LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

if (!$admin || count($students) < 2) {
    die("Pas assez d'utilisateurs de test en base.\n");
}

$uAdminId = (int)$admin['id'];
$u1 = $students[0];
$u2 = $students[1];
$u1Id = (int)$u1['id'];
$u2Id = (int)$u2['id'];

echo "Admin: #{$uAdminId} {$admin['prenom']} {$admin['nom']}\n";
echo "Étudiant 1: #{$u1Id} {$u1['prenom']} {$u1['nom']}\n";
echo "Étudiant 2: #{$u2Id} {$u2['prenom']} {$u2['nom']}\n";

$signalementModel = new Signalement($pdo);
$moderationService = new ModerationService($pdo);

// Create a test zone and category
$pdo->exec("INSERT INTO zones (nom, description, type_zone, visibilite, est_active) VALUES ('Zone Test Mod', 'Test', 'filiere', 'public', 1)");
$zoneId = (int)$pdo->lastInsertId();

$pdo->exec("INSERT INTO categories (zone_id, nom, slug, description, est_active) VALUES ({$zoneId}, 'Cat Test', 'cat-test', 'Test', 1)");
$catId = (int)$pdo->lastInsertId();

// Add u1 and u2 to zone
$pdo->exec("INSERT IGNORE INTO appartenance_zones (utilisateur_id, zone_id, role_dans_zone) VALUES ({$u1Id}, {$zoneId}, 'membre'), ({$u2Id}, {$zoneId}, 'membre')");

// Create a test post by u2
$pdo->prepare("INSERT INTO publications (utilisateur_id, zone_id, categorie_id, titre, contenu, type_publication, statut, date_creation) VALUES (?, ?, ?, 'Post Test Inapproprié', 'Contenu à signaler', 'discussion', 'ouvert', NOW())")
    ->execute([$u2Id, $zoneId, $catId]);
$postId = (int)$pdo->lastInsertId();

// Create a test comment by u2 on that post
$pdo->prepare("INSERT INTO commentaires (publication_id, utilisateur_id, contenu, date_creation) VALUES (?, ?, 'Commentaire agressif test', NOW())")
    ->execute([$postId, $u2Id]);
$commentId = (int)$pdo->lastInsertId();

echo "Objets de test créés : Zone #{$zoneId}, Post #{$postId}, Comment #{$commentId}\n";

// --- TEST 1 : RÈGLE ANTI-AUTO-SIGNALEMENT ---
echo "\n--- Test 1 : Anti-auto-signalement ---\n";
$resSelfPub = $signalementModel->signaler($u2Id, 'publication', $postId, 'Harcèlement / agressivité');
assert($resSelfPub['success'] === false, "Auto-signalement post devrait échouer");
echo "✓ Auto-signalement post bloqué : " . $resSelfPub['message'] . "\n";

$resSelfCom = $signalementModel->signaler($u2Id, 'commentaire', $commentId, 'Spam / publicité');
assert($resSelfCom['success'] === false, "Auto-signalement commentaire devrait échouer");
echo "✓ Auto-signalement com bloqué : " . $resSelfCom['message'] . "\n";

$resSelfUsr = $signalementModel->signaler($u2Id, 'utilisateur', $u2Id, 'Autre');
assert($resSelfUsr['success'] === false, "Auto-signalement compte devrait échouer");
echo "✓ Auto-signalement profil bloqué : " . $resSelfUsr['message'] . "\n";

// --- TEST 2 : SIGNALEMENT VALIDE PAR U1 ---
echo "\n--- Test 2 : Signalement valide par U1 ---\n";
$resPub = $signalementModel->signaler($u1Id, 'publication', $postId, 'Harcèlement / agressivité', 'Propos déplacés répétés', $zoneId);
assert($resPub['success'] === true, "Signalement publication devrait réussir");
$repPubId = (int)$resPub['report_id'];
echo "✓ Signalement publication créé avec succès : Report #{$repPubId}\n";

// --- TEST 3 : RÈGLE ANTI-DOUBLON ---
echo "\n--- Test 3 : Anti-doublon ---\n";
$resDup = $signalementModel->signaler($u1Id, 'publication', $postId, 'Spam');
assert($resDup['success'] === false, "Doublon devrait échouer");
echo "✓ Doublon bloqué : " . $resDup['message'] . "\n";

// --- TEST 4 : SIGNALEMENT D'UN UTILISATEUR ET D'UN COMMENTAIRE ---
echo "\n--- Test 4 : Signalement d'un profil et d'un commentaire ---\n";
$resCom = $signalementModel->signaler($u1Id, 'commentaire', $commentId, 'Contenu inapproprié', 'Insultes en public', $zoneId);
assert($resCom['success'] === true, "Signalement commentaire devrait réussir");
$repComId = (int)$resCom['report_id'];
echo "✓ Signalement commentaire créé : Report #{$repComId}\n";

$resUser = $signalementModel->signaler($u1Id, 'utilisateur', $u2Id, 'Usurpation / faux compte', 'Profil suspect', $zoneId);
assert($resUser['success'] === true, "Signalement utilisateur devrait réussir");
$repUserId = (int)$resUser['report_id'];
echo "✓ Signalement utilisateur créé : Report #{$repUserId}\n";

// --- TEST 5 : QUOTA DE 5 SIGNALEMENTS PAR JOUR ---
echo "\n--- Test 5 : Quota de 5 signalements max par jour ---\n";
// Create another post
$pdo->prepare("INSERT INTO publications (utilisateur_id, zone_id, categorie_id, titre, contenu, type_publication, statut, date_creation) VALUES (?, ?, ?, 'Post Test 4', 'Contenu 4', 'discussion', 'ouvert', NOW())")
    ->execute([$u2Id, $zoneId, $catId]);
$post4Id = (int)$pdo->lastInsertId();
$pdo->prepare("INSERT INTO publications (utilisateur_id, zone_id, categorie_id, titre, contenu, type_publication, statut, date_creation) VALUES (?, ?, ?, 'Post Test 5', 'Contenu 5', 'discussion', 'ouvert', NOW())")
    ->execute([$u2Id, $zoneId, $catId]);
$post5Id = (int)$pdo->lastInsertId();
$pdo->prepare("INSERT INTO publications (utilisateur_id, zone_id, categorie_id, titre, contenu, type_publication, statut, date_creation) VALUES (?, ?, ?, 'Post Test 6', 'Contenu 6', 'discussion', 'ouvert', NOW())")
    ->execute([$u2Id, $zoneId, $catId]);
$post6Id = (int)$pdo->lastInsertId();

$res4 = $signalementModel->signaler($u1Id, 'publication', $post4Id, 'Spam / publicité'); // 4e
$res5 = $signalementModel->signaler($u1Id, 'publication', $post5Id, 'Spam / publicité'); // 5e
$res6 = $signalementModel->signaler($u1Id, 'publication', $post6Id, 'Spam / publicité'); // 6e -> QUOTA DÉPASSÉ
assert($res6['success'] === false, "6e signalement devrait être rejeté par le quota");
echo "✓ Quota 5/jour bloqué correctement : " . $res6['message'] . "\n";

// --- TEST 6 : MODÉRATION - AVERTIR L'AUTEUR ---
echo "\n--- Test 6 : Modération - Avertir l'auteur ---\n";
$warnOk = $moderationService->avertirAuteur($repPubId, $uAdminId, 'Attention à vos propos');
assert($warnOk === true, "Avertir l'auteur devrait réussir");
$checkRep = $pdo->query("SELECT statut, action_prise, traite_par FROM signalements WHERE id = {$repPubId}")->fetch(PDO::FETCH_ASSOC);
assert($checkRep['statut'] === 'traite' && $checkRep['action_prise'] === 'avertissement', "Statut et action_prise incorrects");
echo "✓ Avertissement enregistré : statut = {$checkRep['statut']}, action = {$checkRep['action_prise']}\n";

// --- TEST 7 : MODÉRATION - SUPPRIMER LE CONTENU ---
echo "\n--- Test 7 : Modération - Supprimer le contenu ---\n";
$delOk = $moderationService->supprimerContenu($repComId, $uAdminId, 'Supprimé pour non-respect de la charte');
assert($delOk === true, "Suppression contenu devrait réussir");
$comCount = $pdo->query("SELECT COUNT(*) FROM commentaires WHERE id = {$commentId}")->fetchColumn();
assert((int)$comCount === 0, "Commentaire devrait être supprimé physiquement");
$checkRepCom = $pdo->query("SELECT statut, action_prise FROM signalements WHERE id = {$repComId}")->fetch(PDO::FETCH_ASSOC);
assert($checkRepCom['statut'] === 'traite' && $checkRepCom['action_prise'] === 'suppression_contenu', "Action prise suppression_contenu attendue");
echo "✓ Commentaire supprimé en BD et signalement marqué traite/suppression_contenu\n";

// --- TEST 8 : MODÉRATION - EXPULSER DU SALON ---
echo "\n--- Test 8 : Modération - Expulser du salon ---\n";
// Create another report on u2 for the zone
$pdo->prepare("INSERT INTO signalements (auteur_id, type_cible, cible_id, motif, zone_id, statut, date_signalement) VALUES (?, 'utilisateur', ?, 'Perturbation salon', ?, 'en_attente', NOW())")
    ->execute([$u1Id, $u2Id, $zoneId]);
$repExpelId = (int)$pdo->lastInsertId();

$expelOk = $moderationService->expulserDuSalon($repExpelId, $uAdminId, 'Expulsé pour perturbations répétées');
assert($expelOk === true, "Expulser du salon devrait réussir");
$inZone = $pdo->query("SELECT COUNT(*) FROM appartenance_zones WHERE zone_id = {$zoneId} AND utilisateur_id = {$u2Id}")->fetchColumn();
assert((int)$inZone === 0, "Utilisateur ne doit plus être dans la zone");
$checkRepExpel = $pdo->query("SELECT statut, action_prise FROM signalements WHERE id = {$repExpelId}")->fetch(PDO::FETCH_ASSOC);
assert($checkRepExpel['statut'] === 'traite' && $checkRepExpel['action_prise'] === 'expulsion_salon', "Action expulsion_salon attendue");
echo "✓ Utilisateur expulsé de la zone et signalement clôturé avec expulsion_salon\n";

// --- TEST 9 : MODÉRATION - CLASSER SANS SUITE (REJETER) ---
echo "\n--- Test 9 : Modération - Classer sans suite ---\n";
$rejOk = $moderationService->rejeterSignalement($repUserId, $uAdminId);
assert($rejOk === true, "Rejeter signalement devrait réussir");
$checkRepUser = $pdo->query("SELECT statut, action_prise FROM signalements WHERE id = {$repUserId}")->fetch(PDO::FETCH_ASSOC);
assert($checkRepUser['statut'] === 'rejete' && $checkRepUser['action_prise'] === 'aucune', "Action aucune attendue pour rejet");
echo "✓ Signalement classé sans suite avec statut = rejete et action_prise = aucune\n";

// --- TEST 10 : MODÉRATION - SUSPENDRE LE COMPTE (ADMIN ONLY) ---
echo "\n--- Test 10 : Modération - Suspendre le compte ---\n";
// Create a temporary dummy user to test suspension
$pdo->exec("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, est_actif) VALUES ('Susp', 'Test', 'susptest@example.com', 'xxx', 'etudiant', 1)");
$dummyId = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO signalements (auteur_id, type_cible, cible_id, motif, statut, date_signalement) VALUES (?, 'utilisateur', ?, 'Fraude grave', 'en_attente', NOW())")
    ->execute([$u1Id, $dummyId]);
$repSuspId = (int)$pdo->lastInsertId();

// Non-admin attempt must fail:
$failSusp = $moderationService->suspendreCompte($repSuspId, $u1Id, 'Tentative non admin');
assert($failSusp === false, "Suspension par non-admin doit être rejetée");
echo "✓ Tentative de suspension par un non-admin refusée\n";

// Admin attempt succeeds:
$okSusp = $moderationService->suspendreCompte($repSuspId, $uAdminId, 'Compte suspendu pour fraude');
assert($okSusp === true, "Suspension par admin doit réussir");
$dummyActive = $pdo->query("SELECT est_actif FROM utilisateurs WHERE id = {$dummyId}")->fetchColumn();
assert((int)$dummyActive === 0, "Le compte dummy doit être inactif (est_actif = 0)");
$checkRepSusp = $pdo->query("SELECT statut, action_prise FROM signalements WHERE id = {$repSuspId}")->fetch(PDO::FETCH_ASSOC);
assert($checkRepSusp['statut'] === 'traite' && $checkRepSusp['action_prise'] === 'suspension_compte', "Action suspension_compte attendue");
echo "✓ Compte suspendu avec succès par l'administrateur (est_actif = 0)\n";

// --- TEST 11 : getAllReports AVEC ENRICHISSEMENTS ET FILTRES ---
echo "\n--- Test 11 : getAllReports() avec filtres et enrichissements ---\n";
$allReports = $signalementModel->getAllReports();
assert(count($allReports) > 0, "Doit retourner des signalements");
$firstRep = $allReports[0];
assert(isset($firstRep['action_prise']), "Le champ action_prise doit être présent");
assert(isset($firstRep['detail']), "Le champ detail doit être présent");
assert(isset($firstRep['resolved_zone_id']), "Le champ resolved_zone_id doit être présent");
echo "✓ getAllReports() retourne tous les champs enrichis requis\n";

// --- NETTOYAGE ---
echo "\n--- Nettoyage des données de test ---\n";
$pdo->exec("DELETE FROM signalements WHERE auteur_id = {$u1Id} OR cible_id IN ({$postId}, {$commentId}, {$dummyId})");
$pdo->exec("DELETE FROM notifications WHERE contenu LIKE '%signalement%' OR contenu LIKE '%Avertissement%'");
$pdo->exec("DELETE FROM commentaires WHERE id = {$commentId}");
$pdo->exec("DELETE FROM publications WHERE id IN ({$postId}, {$post4Id}, {$post5Id}, {$post6Id})");
$pdo->exec("DELETE FROM appartenance_zones WHERE zone_id = {$zoneId}");
$pdo->exec("DELETE FROM categories WHERE id = {$catId}");
$pdo->exec("DELETE FROM zones WHERE id = {$zoneId}");
$pdo->exec("DELETE FROM utilisateurs WHERE id = {$dummyId}");
echo "✓ Nettoyage terminé avec succès\n";

echo "\n============================================\n";
echo "TOUS LES 11 TESTS ONT RÉUSSI SANS AUCUNE ERREUR !\n";
echo "============================================\n";
