<?php

namespace Uspdev\Replicado;

class Convenio extends ReplicadoBase {

    /**
     * Compatível com o novo sistema CONVENIOS do Portal USP 
     * 
     * Método para listar convênios acadêmicos ativos
     * 
     * O parâmetro $codtipasu são os valores para os diversos tipos de convênios.
     * 
     * Para obter estes códigos de tipo de convênio:
     * SELECT DISTINCT codtipasu, dsccvn FROM CVCONVENIO ORDER BY codtipasu;
     * 
     * @param array $codtipasuArray - Define os tipos de convênios requeridos
     * @return array - Retorna um array associativo contendo os convênios, seus coordenadores e organizações.
     *  [codcvn, nomeConvenio, tipoConvenio, coordenadores, dataInicio, dataFim]
     * @author Erickson Zanon @ezanon
     * 
     */
    protected static function _listarConveniosAcademicosAprovadosUSP(array $codtipasuArray = [23, 27]) {
        $codtipasu = implode(',',$codtipasuArray);
        $query = DB::getQuery('Convenio.listarConveniosAcademicosAprovadosUSP.sql');
        $query = str_replace('__codtipasu__',$codtipasu,$query);     
        $convenios = DB::fetchAll($query);
        // 🔹 Converte datas (mantendo compatibilidade MSSQL/Sybase)
        foreach ($convenios as $key => $conv) {
            $convenios[$key]['dataInicio'] = !empty($conv['dataInicio']) ? date('d/m/Y', strtotime($conv['dataInicio'])) : '—';
            $convenios[$key]['dataFim'] = !empty($conv['dataFim']) ? date('d/m/Y', strtotime($conv['dataFim'])) : '—';
            // 🔹 Obtém organizações
            $orgs = self::_listarOrganizacoesConvenioUSP($convenios[$key]['codcvn']);
            $convenios[$key]['organizacoes'] = '';
            foreach ($orgs as $org) {
                $nomeOrg = $org['nomeOrganizacao'];
                $convenios[$key]['organizacoes'] .= $convenios[$key]['organizacoes'] == '' ? $nomeOrg : '|' . $nomeOrg;
            }
        }
        return $convenios;
    }
    
     /**
     * Compatível com o novo sistema CONVENIOS do Portal USP 
     * Método para listar as organizações de um convênio acadêmico
     * 
     * @param array $codcvnusp - código do convênio
     * @return string - Retorna um array associativo contendo os convênios, seus coordenadores e organizações.
     *  [codcvn, nomeConvenio, tipoConvenio, coordenadores, dataInicio, dataFim]
     * @author Erickson Zanon @ezanon
     * 
     */
    protected static function _listarOrganizacoesConvenioUSP($codcvnusp) {
        $query = DB::getQuery('Convenio.listarOrganizacoesConvenioUSP.sql');
        $params = [
            'codcvnusp' => $codcvnusp
        ];
        $organizacoes = DB::fetchAll($query, $params);
        return $organizacoes;        
    }
    
/**
 * 
 * Abaixo, métodos compatíveis com o eConvenios (Mercúrio)
 * 
 * Para dados mais recentes usar os métodos terminados com USP
 * 
 */
    

    /**
     * Compatível com o novo antigo eCONVENIOS do Mercurio
     * 
     * Método para listar convênios acadêmicos internacionais.
     *
     * Quando o parâmetro $ativos for verdadeiro, carrega apenas convênios ativos,
     * conforme definido na consulta 'Convenios.listarConveniosAcademicosInternacionais.sql'.
     * Além dos dados principais, o método também agrega as informações de coordenadores e
     * organizações associadas a cada convênio, unificando-os em strings separadas por '|'.
     *
     * O array de saída contém os seguintes campos:
     * - codcvn: código do convênio
     * - nomeConvenio: nome do convênio
     * - dataInicio: data de início do convênio (formato dd/mm/yyyy)
     * - dataFim: data de término do convênio (formato dd/mm/yyyy)
     * - coordenadores: nomes dos coordenadores vinculados (separados por '|')
     * - organizacoes: nomes das organizações vinculadas (separados por '|')
     *
     * @param bool $ativos - Define se devem ser listados apenas convênios ativos (true) ou todos (false).
     * @return array - Retorna um array associativo contendo os convênios, seus coordenadores e organizações.
     * @author Erickson Zanon <ezanon@gmail.com>
     */
    protected static function _listarConveniosAcademicosInternacionais($ativos = true) {
        // Define qual consulta usar
        if ($ativos) {
            $query = DB::getQuery('Convenio.listarConveniosAcademicosInternacionais.sql');
        } else {
            $query = DB::getQuery('Convenio.listarConveniosAcademicosInternacionaisInativos.sql');
        }

        $convenios = DB::fetchAll($query);

        // Processa relacionamentos e formata datas
        foreach ($convenios as $key => $conv) {

            $codcvn = $conv['codcvn'];

            // 🔹 Converte datas (mantendo compatibilidade MSSQL/Sybase)
            $inicio = !empty($conv['dataInicio']) ? date('d/m/Y', strtotime($conv['dataInicio'])) : '—';
            $fim = !empty($conv['dataFim']) ? date('d/m/Y', strtotime($conv['dataFim'])) : '—';
            $convenios[$key]['dataInicio'] = $inicio;
            $convenios[$key]['dataFim'] = $fim;

            // 🔹 Obtém responsáveis
            $resps = self::_listarCoordenadoresConvenio($codcvn);
            $convenios[$key]['coordenadores'] = '';
            foreach ($resps as $resp) {
                $nome = $resp['nompesttd'];
                $convenios[$key]['coordenadores'] .= $convenios[$key]['coordenadores'] == '' ? $nome : '|' . $nome;
            }

            // 🔹 Obtém organizações
            $orgs = self::_listarOrganizacoesConvenio($codcvn);
            $convenios[$key]['organizacoes'] = '';
            foreach ($orgs as $org) {
                $nomeOrg = $org['nomeOrganizacao'];
                $convenios[$key]['organizacoes'] .= $convenios[$key]['organizacoes'] == '' ? $nomeOrg : '|' . $nomeOrg;
            }
        }

        return $convenios;
    }

    /**
     * Compatível com o novo antigo eCONVENIOS do Mercurio
     * 
     * Método para listar os responsáveis vinculados a um convênio específico.
     *
     * Utiliza a consulta 'Convenios.listarCoordenadoresConvenio.sql' para obter os registros
     * de responsáveis associados ao código do convênio informado.
     *
     * O array de saída contém os seguintes campos:
     * - codcvn: código do convênio
     * - codpes: código do coordenador
     * - nompesttd: nome social da pessoa (se houver)
     *
     * @param int $codcvn - Código do convênio cujos coordenadores serão consultados.
     * @return array - Retorna um array associativo contendo os coordenadores do convênio.
     * @author Erickson Zanon <ezanon@gmail.com>
     */
    protected static function _listarCoordenadoresConvenio($codcvn) {
        $query = DB::getQuery('Convenio.listarCoordenadoresConvenio.sql');
        $params = [
            'codcvn' => $codcvn
        ];
        $responsaveis = DB::fetchAll($query, $params);

        return $responsaveis;
    }

    /**
     * Compatível com o novo antigo eCONVENIOS do Mercurio
     * 
     * Método para listar as organizações externas vinculadas a um convênio específico.
     *
     * Utiliza a consulta 'Convenios.listarOrganizacoesConvenio.sql' para obter as organizações
     * relacionadas ao convênio informado, conforme seu código.
     *
     * O array de saída contém os seguintes campos:
     * - codcvn: código do convênio
     * - codorg: código da organização vinculada
     * - nomeOrganizacao: razão social da organização
     *
     * @param int $codcvn - Código do convênio cujas organizações serão consultadas.
     * @return array - Retorna um array associativo contendo as organizações do convênio.
     * @author Erickson Zanon <ezanon@gmail.com>
     */
    protected static function _listarOrganizacoesConvenio($codcvn) {
        $query = DB::getQuery('Convenio.listarOrganizacoesConvenio.sql');
        $params = [
            'codcvn' => $codcvn
        ];
        $organizacoes = DB::fetchAll($query, $params);

        return $organizacoes;
    }
}
