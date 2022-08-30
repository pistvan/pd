WITH eladasok AS (
	SELECT
		eladas.*,
		-- eladási deviza árfolyama (vagy alap árfolyama) HUF-ban
		IFNULL(arfolyam.arfolyam, deviza.alap_arfolyam) deviza_arfolyam,
		-- EUR deviza árfolyama (vagy alap árfolyama) HUF-ban
		IFNULL(arfolyam_eur.arfolyam, deviza_eur.alap_arfolyam) eur_arfolyam
	FROM eladas
	-- hozzákapcsoljuk az adott hónap-beli, adott devizához tartozó árfolyamot
	LEFT JOIN arfolyam
		ON YEAR(eladas.datum) = arfolyam.ev
		AND MONTH(eladas.datum) = arfolyam.ho
		AND eladas.deviza_id = arfolyam.deviza_id
	-- hozzákapcsoljuk az adott hónap-beli EUR-árfolyamot
	LEFT JOIN arfolyam arfolyam_eur
		ON YEAR(eladas.datum) = arfolyam_eur.ev
		AND MONTH(eladas.datum) = arfolyam_eur.ho
		AND arfolyam_eur.deviza_id = 2 -- EUR
	-- hozzákapcsoljuk a devizát
	LEFT JOIN deviza
		ON eladas.deviza_id = deviza.id
	-- hozzákapcsoljuk az EUR devizát
	LEFT JOIN deviza deviza_eur
		ON deviza_eur.id = 2 -- EUR
)
SELECT
	YEAR(eladasok.datum) ev,
	SUM(eladasok.mennyiseg * eladasok.ar * eladasok.deviza_arfolyam) forgalom_huf,
	SUM(eladasok.mennyiseg * eladasok.ar * eladasok.deviza_arfolyam / eladasok.eur_arfolyam) forgalom_eur
FROM eladasok
GROUP BY 1;
