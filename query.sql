WITH eladasok AS (
	SELECT
		eladas.*,
		-- eladási deviza árfolyama (vagy alap árfolyama) HUF-ban
		IFNULL(arfolyam.arfolyam, deviza.alap_arfolyam) deviza_arfolyam
	FROM eladas
	-- hozzákapcsoljuk az adott hónap-beli, adott devizához tartozó árfolyamot
	LEFT JOIN arfolyam
		ON YEAR(eladas.datum) = arfolyam.ev
		AND MONTH(eladas.datum) = arfolyam.ho
		AND eladas.deviza_id = arfolyam.deviza_id
	-- hozzákapcsoljuk a devizát
	LEFT JOIN deviza
		ON eladas.deviza_id = deviza.id
)
SELECT
	YEAR(eladasok.datum) ev,
	SUM(eladasok.mennyiseg * eladasok.ar * eladasok.deviza_arfolyam) forgalom_huf
FROM eladasok
GROUP BY 1;
