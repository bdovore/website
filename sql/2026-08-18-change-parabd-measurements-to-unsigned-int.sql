-- Les dimensions en millimètres et le poids en grammes sont des entiers.
ALTER TABLE `parabd_item`
    MODIFY `WIDTH_MM` INT UNSIGNED NULL,
    MODIFY `HEIGHT_MM` INT UNSIGNED NULL,
    MODIFY `DEPTH_MM` INT UNSIGNED NULL,
    MODIFY `WEIGHT_G` INT UNSIGNED NULL;
