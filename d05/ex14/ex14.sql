SELECT floor_number AS `floor`, SUM(nb_seats) AS `seats`
FROM `db_lsandor-`.cinema
GROUP BY `floor`
ORDER BY `seats` DESC;