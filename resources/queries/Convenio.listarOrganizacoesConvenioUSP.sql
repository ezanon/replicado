SELECT 
    co.codcvnusp,
    co.codorg,
    o.nomrazsoc AS nomeOrganizacao
FROM CVCONVORGANIZACAO co
JOIN ORGANIZACAO o ON o.codorg = co.codorg
WHERE co.codcvnusp = convert(int,:codcvnusp)