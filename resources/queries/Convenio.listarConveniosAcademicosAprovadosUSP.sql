SELECT 
    cc.codcvnusp as codcvn,
    cc.titcvn AS nomeConvenio, 
    cc.dsccvn AS tipoConvenio,
    p.nompes AS coordenadores,
    c.dtaasicvn AS dataInicio,
    c.dtadtvcvn AS dataFim
FROM CVCONVENIO cc
JOIN CONVENIO c ON cc.codcvnusp = c.codcvn 
JOIN PESSOA p ON cc.codpescdn = p.codpes 
JOIN CVCONVUNIDDESP u ON cc.codcvnusp = u.codcvnusp 
WHERE 
    cc.codtipasu IN (__codtipasu__) 
    AND c.stacvn = 'Aprovado'
    AND u.codunddsp IN (__codundclg__)
    AND c.dtadtvcvn IS NOT NULL
    AND GETDATE() BETWEEN c.dtaasicvn AND c.dtadtvcvn	
ORDER BY c.dtaasicvn;
