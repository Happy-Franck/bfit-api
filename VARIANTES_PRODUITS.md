# Système de Variantes de Produits

## Vue d'ensemble

Ce système permet de gérer les variantes de produits avec des attributs multiples comme la taille, la couleur, le poids, etc.

## Structure des Tables

### 1. `product_attributes`
Définit les types d'attributs possibles :
- Taille (S, M, L, XL)
- Couleur (Rouge, Vert, Bleu) 
- Poids (5kg, 8kg, 10kg)

### 2. `product_attribute_values`
Stocke les valeurs spécifiques de chaque attribut :
- Pour "Taille" : XS, S, M, L, XL, XXL
- Pour "Couleur" : Noir, Blanc, Rouge, etc.
- Pour "Poids" : 5kg, 8kg, 10kg, etc.

### 3. `product_variants`
Représente chaque variante unique d'un produit :
- Pull Rouge Taille M
- Haltère 8kg
- Protéine 2kg

### 4. `product_variant_attributes`
Lie les variantes aux valeurs d'attributs

### 5. `product_product_attributes`
Défini quels attributs sont utilisés par chaque produit

## Exemples d'utilisation

### Exemple 1 : Pull avec Taille et Couleur

```sql
-- 1. Créer le produit principal
INSERT INTO produits (name, image, description, price, product_type_id) 
VALUES ('Pull Nike', 'pull-nike.jpg', 'Pull de sport confortable', 25000, 1);

-- 2. Lier le produit aux attributs Taille et Couleur
INSERT INTO product_product_attributes (product_id, product_attribute_id) 
VALUES (1, 1), (1, 2); -- 1=Taille, 2=Couleur

-- 3. Créer les variantes
INSERT INTO product_variants (product_id, sku, name, price, stock_quantity) 
VALUES 
(1, 'PULL-NIKE-M-NOIR', 'Pull Nike M Noir', 25000, 10),
(1, 'PULL-NIKE-M-ROUGE', 'Pull Nike M Rouge', 25000, 8),
(1, 'PULL-NIKE-L-NOIR', 'Pull Nike L Noir', 25000, 5);

-- 4. Lier les variantes aux valeurs d'attributs
INSERT INTO product_variant_attributes (product_variant_id, product_attribute_value_id) 
VALUES 
(1, 3), (1, 7), -- M + Noir
(2, 3), (2, 9), -- M + Rouge  
(3, 4), (3, 7); -- L + Noir
```

### Exemple 2 : Haltère avec Poids uniquement

```sql
-- 1. Créer le produit principal
INSERT INTO produits (name, image, description, price, product_type_id) 
VALUES ('Haltère Professionnel', 'haltere.jpg', 'Haltère de qualité pro', 15000, 2);

-- 2. Lier le produit à l'attribut Poids
INSERT INTO product_product_attributes (product_id, product_attribute_id) 
VALUES (2, 3); -- 3=Poids

-- 3. Créer les variantes avec prix différents
INSERT INTO product_variants (product_id, sku, name, price, stock_quantity) 
VALUES 
(2, 'HALTERE-5KG', 'Haltère 5kg', 15000, 20),
(2, 'HALTERE-8KG', 'Haltère 8kg', 20000, 15),
(2, 'HALTERE-10KG', 'Haltère 10kg', 23000, 10);
```

### Exemple 3 : Protéine avec Poids

```sql
-- 1. Créer le produit principal
INSERT INTO produits (name, image, description, price, product_type_id) 
VALUES ('Whey Protein', 'whey.jpg', 'Protéine de haute qualité', 45000, 3);

-- 2. Lier le produit à l'attribut Poids
INSERT INTO product_product_attributes (product_id, product_attribute_id) 
VALUES (3, 3); -- 3=Poids

-- 3. Créer les variantes
INSERT INTO product_variants (product_id, sku, name, price, stock_quantity) 
VALUES 
(3, 'WHEY-1KG', 'Whey Protein 1kg', 45000, 30),
(3, 'WHEY-2KG', 'Whey Protein 2kg', 47000, 25),
(3, 'WHEY-5KG', 'Whey Protein 5kg', 55000, 10);
```

## Commandes pour initialiser

```bash
# Exécuter les migrations
php artisan migrate

# Exécuter le seeder pour les attributs
php artisan db:seed --class=ProductVariantsSeeder
```

## Utilisation dans l'interface

1. **Créer un produit** : Le produit principal avec nom, description, image générale
2. **Sélectionner les attributs** : Choisir quels attributs ce produit utilise
3. **Générer les variantes** : Créer toutes les combinaisons possibles
4. **Définir prix et stock** : Ajuster le prix et stock pour chaque variante
5. **Gérer les images** : Ajouter des images spécifiques par variante si nécessaire

## Avantages

- ✅ Gestion flexible des attributs
- ✅ Prix différents par variante
- ✅ Stock indépendant par variante
- ✅ Images spécifiques par variante
- ✅ Facilité d'ajout de nouveaux attributs
- ✅ Système évolutif et modulaire 