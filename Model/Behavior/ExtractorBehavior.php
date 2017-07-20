<?php
/* 
 * Used to extract vectors
 */
// App::uses('Sanitize', 'Utility');

class ExtractorBehavior extends ModelBehavior 
{
	// model/instance specific settings
	public $settings = array();
	
	// default settings
	protected $_defaults = array(
		// keep the order 
		'extractors' => array(
			'email' => array(
				'regex' => '/([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})/i',
				'regex_validate' => '/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/i',
				'remove_string' => true,
				'whitelist' => array(),
			),
			'partial_email' => array(
				'regex' => '/^@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})/i',
				'regex_validate' => '/^@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/i',
				'remove_string' => true,
				'whitelist' => array(),
			),
			'url' => array(
				'regex' => '/\b((mailto\:|(news|(ht|f)tp(s?))\:\/\/){1}[A-Za-z0-9.\/\?\-\_]+)\b/i',
				'regex_validate' => '/^((mailto\:|(news|(ht|f)tp(s?))\:\/\/){1}[A-Za-z0-9.\/\?\-\_]+)$/i',
				'remove_string' => false,
				'whitelist' => array(),
				'obfuscate' => array(
					'.' => '[.]',
					':' => '[:]',
				),
			),
			'hostname' => array(
				'regex' => '/[\_\-\.a-z0-9]{2,}\.((aero|arpa|a[cdefgilmnoqrstuwxz]+)|(biz|b[abdefghijmnorstvwyz]+)|(cat|com|coop|c[acdfghiklmnorsuvxyz]+)|d[ejkmoz]+|(edu|e[ceghrstu]+)|f[ijkmor]+|(gov|g[abdefghilmnpqrstuwy]+)|h[kmnrtu]+|(info|int|i[delmnoqrst]+)|(jobs|j[emop]+)|k[eghimnprwyz]+|(local|l[abcgiknrstuvy]+)|(mil|mobi|museum|m[acdeghklmnopqrstuvwxyz]+)|(name|net|n[acefgilopruz]+)|(om|org)|(pro|p[aefghklmnprstwy]+)|qa|r[eouw]+|s[abcdeghijklmnortvyz]+|(travel|t[cdfghjklmnoprtvwz]+)|u[agkmsyz]+|v[aceginu]+|w[fs]+|x[yz]+|y[etu]+|z[amw]+)[^a-zA-Z\.\?]/i',
				'regex_validate' => '/^[\_\-\.a-z0-9]{2,}\.((aero|arpa|a[cdefgilmnoqrstuwxz]+)|(biz|b[abdefghijmnorstvwyz]+)|(cat|com|coop|c[acdfghiklmnorsuvxyz]+)|d[ejkmoz]+|(edu|e[ceghrstu]+)|f[ijkmor]+|(gov|g[abdefghilmnpqrstuwy]+)|h[kmnrtu]+|(info|int|i[delmnoqrst]+)|(jobs|j[emop]+)|k[eghimnprwyz]+|(local|l[abcgiknrstuvy]+)|(mil|mobi|museum|m[acdeghklmnopqrstuvwxyz]+)|(name|net|n[acefgilopruz]+)|(om|org)|(pro|p[aefghklmnprstwy]+)|qa|r[eouw]+|s[abcdeghijklmnortvyz]+|(travel|t[cdfghjklmnoprtvwz]+)|u[agkmsyz]+|v[aceginu]+|w[fs]+|x[yz]+|y[etu]+|z[amw]+)$/i',
				'regex_replace' => array(
					'/(,|\/)$/i' => '',
				),
				'remove_string' => true,
				'whitelist' => array(
					'#^\.\.+#i',
					'/\.(htm|php|css|lang|for|gif|png|pl|tmp|pf)$/i', // file names
					'/\.{2,}/i', // example....com
				),
				'obfuscate' => array(
					'.' => '[.]',
				),
			),
			'ipaddress' => array(
				'regex' => '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/i',
				'regex_validate' => '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i',
				'remove_string' => true,
				'whitelist' => array(
					'/\.{2,}/i', // example....com
				),
				'obfuscate' => array(
					'.' => '[.]',
				),
			),
			'filename' => array(
				'regex' => '/\b[\SA-Za-z0-9\.\?\-\_]+\.(desklink|numbers|dvdproj|balance|torrent|graffle|package|naplps|sldprt|emaker|adicht|kbasic|udiwww|slddrw|policy|vbproj|fsproj|sldasm|hppcl|plist|spiff|grdnt|vicar|mlraw|proto|ccitt|blend|flame|grasp|pages|sctor|accdu|bpoly|accdt|accde|accda|accdb|dylib|tpoly|saver|class|ffivw|jbig|djvu|jfif|vmdk|gmod|docm|jpeg|vinf|viff|odif|ftxt|fweb|qtvr|objf|ecms|aspx|asmx|nitf|ecmt|smpl|sndb|dotx|kdbx|vrml|olsr|spin|qhcp|pack|soar|docx|cals|php3|php4|cmrl|pict|tddd|cweb|trif|trib|tria|poly|topc|tmy2|cpmz|plsc|tiff|html|plsk|text|hpgl|term|ply2|iges|dats|grib|cats|isma|pseg|fsim|vala|jasc|bufr|par2|part|irtr|uoml|indd|hdmp|pptx|chml|hcgs|info|unif|proj|ingr|java|bdef|face|xlsb|xlsx|sdts|acmb|acis|runz|mdmp|mobi|sdml|sats|film|xspf|m3u8|enff|rtfd|xmpz|xmod|mpkg|wsrc|fits|mpeg|flac|font|midi|mol2|wmdb|shar|rmvb|msqm|safe|saif|msdl|mime|aiff|aifc|miff|prj|pri|prk|rpd|pro|prn|prm|prg|psv|roo|psw|prc|prd|rsy|prf|pre|rpb|prs|psd|psn|psp|rtf|rpm|rpt|rrd|psm|pse|rsc|rpl|psb|prt|psf|prr|rsp|rsl|prx|psq|psa|rpf|pst|ppp|rvw|pmc|pm5|pm4|pm3|pmi|pmm|pn3|pnf|rus|rvf|pmp|rws|ply|pln|plp|plm|rzx|pll|pls|rzk|rwx|plt|rwz|rxd|png|pnm|ppl|ppm|ppk|ppj|ppd|ppo|pt3|pr1|pr2|rtl|ppt|pps|ppb|ppa|run|pop|pol|poh|pnt|rul|rts|pow|pov|rtp|pot|pr3|ptf|qlb|qlc|rev|qiz|qhp|qif|qlp|qm4|res|qrt|qrs|qpx|qpr|rex|rez|qdk|qdr|qdf|qcp|qbw|qch|qdv|qef|rgb|rft|qhc|qfx|rgx|qry|req|rar|rdp|ram|ral|r8p|rad|ras|rat|rbf|raw|rcg|rdf|rdi|rdx|r3d|qwk|rel|rem|qvw|rep|qvd|reg|ref|r2d|rec|qxl|red|qxd|qbo|rib|rlz|rle|put|pud|rmb|pub|rld|pvd|rlb|pvt|rlc|pvm|pvl|ptu|ptr|rol|ptg|ray|ptb|pt4|pt5|ptl|rno|ptm|ptn|rmi|rmk|rnd|pwf|pwk|pzx|q3s|rix|pzt|pzp|pzs|rip|qag|qbe|ric|rif|qbb|qap|rl4|pzo|pxp|rl8|pwp|pwl|rla|pxr|pxz|pzd|pzi|pyo|pyi|pyc|rom|pgl|nk2|nlm|nls|nil|nif|ngo|ngs|nlx|not|nsa|nsf|nss|nrw|nrg|npi|npj|nfo|nff|ncd|ncp|ndb|ncc|nbf|nam|nap|ndl|nds|neu|new|net|nes|ndx|neo|nst|nsv|oct|ocx|odb|ocr|ocf|obz|ocd|odf|odg|odt|ofd|off|ods|odp|odl|odm|obv|obt|nts|ntx|nuf|ntr|nth|nsx|ntf|nws|nxt|obr|obs|obj|obd|o01|oaz|mzx|mzg|mpc|mpg|mpl|mpa|mp4|mp2|mp3|mpm|mpp|mpx|mpz|mrb|mpv|mpt|mpq|mpr|mov|mop|a11|mnd|mng|mmo|mmm|mlv|mmf|mnt|mnu|mol|mon|mod|mob|mnx|mny|mrc|mrk|mvi|mvo|mvw|mvf|muz|mtw|mus|mwf|mws|mxt|myp|mze|mxp|mxm|mxf|mxl|mtv|mts|msi|msn|mso|msg|mse|mrs|msc|msp|mss|mtl|mtm|mth|msx|mst|msw|ofm|ofn|pem|peq|per|ped|peb|pdv|pdw|pes|pet|pf|pfk|pfm|pfs|pfc|pfb|pex|pfa|pds|pdn|pcw|pcx|pda|pct|pcs|pcl|pcm|pdb|pdd|pdl|pdm|pdi|pdg|pde|pdf|pft|pgd|pk3|pk4|pka|pk2|pjx|pix|pjt|pkg|pkt|plb|plc|pld|pla|pl3|pl1|pl2|piv|pit|pgs|phd|phm|pgp|pgm|pgi|s19|phn|pho|pie|pif|pic|pi2|php|phr|pck|pcj|oth|otl|otp|otg|otb|ora|org|ots|ott|ovd|ovl|ovr|ov2|ov1|otx|out|opx|opw|okt|olb|old|oif|ohs|ogg|ohb|oli|oma|opn|opt|opj|oom|oof|oog|oxt|p10|pbl|pbm|pbo|pbk|pbi|pba|pbd|pbt|pc3|pcd|pcf|pch|pcc|pcb|pc8|pca|pb1|pat|p80|p86|pa1|p3t|p22|p12|p16|pac|pad|par|pas|pan|pal|paf|pak|ple|sd7|vmc|vmf|vmg|vlm|vis|vim|vir|vms|vob|vpf|vpg|vph|vox|vop|voc|vof|vik|vif|vdo|vdr|veg|vda|vcx|vcr|vcw|vew|vfa|vgr|vic|vid|vgd|vga|vfm|vfn|vpi|vqa|w3m|w3n|w3o|w3h|w3g|w3b|w3d|w3p|w3q|w3z|w40|w44|w3x|w3v|w3t|w3u|w3a|w31|vsm|vsp|vss|vsd|vrs|vrm|vrp|vst|vue|w01|w30|vzt|vxd|vwr|vwx|vcd|vca|tzl|tzx|u3d|tzb|tym|txt|txw|uc2|ucn|ue2|ufi|uha|udw|udo|ucs|udf|txi|txf|tru|trw|try|trs|trn|trk|trm|tse|tsk|tud|tut|tvf|ttf|tsv|tsm|tst|uhs|uif|v10|v2d|vai|uwf|uue|usr|utz|val|van|vbx|vbz|vc6|vbs|vbr|vap|var|usp|use|umb|unf|uni|ult|ulf|uih|uld|unx|uop|url|urt|upo|upd|uos|uot|w95|wad|xif|xla|xlb|xft|xfr|xex|xfn|xlc|xld|xlt|xlv|xlw|xls|xlm|xlk|xll|xdm|xdf|wtt|wwb|wwk|wtr|wtf|wsq|wst|wxh|wxp|xcf|xck|xcl|xbm|xbe|x3d|xar|xmf|xmi|yrd|yst|yuv|ymp|yal|xyw|xyz|z2s|z3d|zed|zer|zip|zdg|zad|z64|z80|xy3|xxe|xpm|xps|xqt|xof|xnf|xml|xmv|xrf|xsf|xwk|xwp|xwd|xvf|xtb|xtp|wsp|wsd|wfp|wft|wfx|wfn|wfm|wfb|wfd|wg1|wg2|wiz|wk1|wk3|wix|win|wid|wim|web|wdm|wav|wax|wb1|was|war|wag|wai|wb2|wbf|wcm|wcp|wdb|wch|wcd|wbk|wbt|wk4|wkb|wpl|wpm|wps|wpk|wpj|wpf|wpg|wq1|wr1|wrp|wrs|ws2|wrl|wrk|wrd|wri|wpd|wp5|wkz|wlk|wma|wks|wkq|wke|wki|wmc|wmf|woc|wow|woa|wnf|wmp|wmv|tre|trd|sld|sli|slk|slc|slb|skp|sky|sll|slp|smk|sml|smm|smf|smc|slt|slv|sit|sis|shk|shm|shp|shg|shb|sgt|sh3|shr|sht|sig|sik|sim|sif|sib|shw|shx|smn|smp|spw|spx|sqd|spt|sps|spp|spr|sqi|sql|srf|srp|srt|src|sqz|sqm|sqn|spm|spl|sno|snp|sol|sng|snd|sms|smt|som|son|spg|spi|spf|spd|sou|spc|sgp|sgi|scd|scf|sch|scc|sca|sc3|sc4|sci|scm|sct|scx|scy|scr|scp|scn|sco|sbt|sbr|saf|sal|sam|sab|s7i|s3i|s3m|san|sar|sbk|sbl|sbp|sbi|sbd|sat|sav|mli|sda|sff|sfi|sfl|sfd|sfb|set|sf2|sfn|sfo|sfx|sg1|sgf|sft|sfs|sfp|sfr|ses|seq|sdl|sdm|sdn|sdk|sdi|sdc|sdf|sdr|sds|sen|sep|sec|sea|sdw|sdx|ssc|ssd|tes|tet|tex|tem|tel|tec|tef|tfa|tfc|tg1|tga|tgz|tfs|tfm|tff|tfh|tdw|tdt|tch|tcl|tct|tbz|tbx|tbr|tbs|tcw|td0|tdk|tdr|tds|tdh|tdf|td2|tdb|the|thm|tp3|tpb|tpc|tos|tok|tmy|toc|tpf|tph|tpz|tr2|trc|tpw|tpu|tpl|tpp|tms|tmp|til|tis|tjl|tif|tic|ths|tib|tlb|tlc|tmf|tmo|tm2|tlp|tld|tlh|tbl|tbk|sty|sub|suf|stx|stw|sts|stu|sui|sum|svg|svq|svs|svd|sup|sun|suo|str|stp|sst|ssv|sta|ssr|ssp|ssf|ssg|stb|stc|stl|stm|sto|stk|sti|std|stf|svw|svx|t2l|t44|t64|t2b|syw|syn|sys|tag|tah|tb1|tb2|tbf|taz|tar|tal|tap|sym|syg|swt|sxc|sxd|swp|swg|svy|swf|sxg|sxi|sy3|syd|sy1|sxw|sxm|sxp|s1k|mmp|dcp|dcs|dct|dcm|dcl|dch|dci|dcu|dcw|ddp|dds|deb|ddi|ddb|dcx|dda|dcf|dcd|dbg|dbk|dbl|dbf|dbd|dba|dbc|dbm|dbo|dc6|dca|dcc|dbx|dbw|dbs|dbt|def|dem|diz|dkb|dld|dis|dir|dig|dip|dlf|dlg|dmf|dmg|dmo|dls|dlp|dll|dlm|dif|dic|dfi|dfl|dfm|dff|dfd|des|dev|dfs|dfv|dht|dia|dib|dhp|dgs|dfx|dgn|db3|db2|cpc|cpd|cpf|con|com|cof|col|cpi|cpl|cpz|cr2|cra|cpt|cps|cpp|cpr|coe|cod|cmf|cmk|cmm|cmd|cmb|cls|clw|cmp|cms|cnf|cnv|cob|cnd|cnc|cmu|cmv|crd|crf|cvp|cvs|cvt|cv4|cut|cuf|cur|cvw|cwk|daa|daf|dat|d64|d4p|cxx|d4d|ctx|ctt|cru|csd|csg|crt|crs|crk|crp|csm|csp|ctk|ctl|ctf|ctc|css|csv|dmp|dms|epi|eps|eqn|epd|env|end|eng|erd|erl|mlb|etw|etx|etf|esh|erm|err|enc|emu|eft|efv|efx|efs|efq|efe|efk|ega|eka|emd|emf|eml|elt|eli|elb|elc|eui|evt|f96|fac|fam|f95|f90|f4v|f77|faq|far|fdb|fds|fdw|fcm|fbm|fas|fax|f4p|f4b|exe|exf|exm|exc|ex3|evy|ewd|exp|ext|f2r|f3r|f4a|f03|f01|exx|ezf|efa|eeb|dsk|dsm|dsn|dsf|dsd|ds4|dsc|dsp|dsr|dt1|dta|dtb|dsy|dsw|dss|dst|ds1|drw|dog|doh|dor|dof|doc|dng|dnt|dos|dot|drp|drs|drv|dpx|dpr|dox|doz|dtf|dtl|ecc|ece|ed5|ebq|ebj|eas|eba|eda|ede|eds|edt|edv|edq|edl|edf|edk|ear|e2d|dvi|dvp|dw2|dvc|dtp|dtm|dto|dwb|dwc|dxp|dyn|dxn|dxf|dwd|dwg|clr|clp|arj|ark|arr|ari|arh|ard|arf|art|arx|ash|asi|ask|asf|ase|asc|asd|arc|ar7|aos|apc|apd|aol|ans|anm|ann|ape|apf|apr|apw|apx|app|apm|api|apl|asm|aso|awa|awd|awk|avt|avs|avi|avr|awm|aws|azz|b1n|b30|azw|azd|axf|axm|avb|ava|asx|asz|at2|asw|ast|asp|ass|at3|atm|aup|aux|auz|aud|aty|atn|att|ani|ana|ad2|ad3|ada|act|aco|acl|acm|adb|adc|adl|adm|adn|adi|adg|add|adf|acf|ace|ab6|ab8|abc|aas|aaf|a80|aac|abf|abk|aca|acb|acc|abs|abr|abm|abp|adr|ads|ale|alg|all|alc|aix|aio|ais|alo|alt|aml|amr|ams|amg|amf|alz|amd|ain|aif|aep|af2|af3|adz|adx|adt|adv|afi|afl|agr|ahk|afw|aft|afm|afp|bac|bad|cbz|ccc|ccf|cbt|cbs|cbm|cbr|cch|ccl|cdf|cdk|cdl|cdb|cda|cco|cct|cbl|cbc|c01|c86|cab|c00|bzm|byu|bz2|cac|cad|cas|cat|cba|cap|can|cal|cam|cdm|cdr|chl|chm|chn|chk|chi|ch4|chd|chp|chr|ckb|cla|cld|cix|cif|cht|chz|ch3|cgm|ceg|cel|cfg|cef|ceb|cdt|cdx|cfl|cfm|cga|cgi|cft|cfp|cfn|cfo|bwr|bwb|bf2|bfc|bfm|bez|bdt|bdm|bdr|bfx|bga|bin|bio|bit|bik|bif|bgi|bib|bdf|bdc|bat|bbl|bbm|bas|bar|bak|bal|bbp|bbs|bct|bcw|bdb|bcp|bco|bcf|bch|bkp|bkw|bsc|bsp|bss|bsa|bs2|brk|brl|bst|bsy|bup|but|buy|bun|bug|btm|btn|brd|bpt|bmk|bmp|bmt|bm3|blk|bld|blg|bna|bnk|bpc|bpp|bot|boo|bnr|bok|feb|eth|izt|j2c|jam|iwp|iwd|ivu|iwa|jar|jas|jet|jff|jif|jcn|jbx|jav|jbd|ivp|ivl|irt|isd|ish|irs|ipt|ipn|ips|iso|ist|ive|ivi|itt|itn|itf|itl|jms|job|kep|kex|key|kdc|kdb|kbm|kcl|kfx|kit|kno|kpl|kpp|kmz|kmy|kmc|kml|kbd|kar|jpg|jre|jrn|jpc|jp2|jor|jou|jsp|jtf|k3d|kap|k2a|jzz|jvx|jwl|ipl|iph|ica|icb|icc|ibm|ibg|iam|iax|ice|icm|ide|idf|ids|idc|idb|icn|ico|iak|hyp|htm|htx|hwd|hst|hsi|hsc|hsh|hwp|hxm|hyc|hyd|hy2|hy1|hxs|hxx|idw|idx|inf|ini|ink|ind|inc|in3|inb|ins|int|ipc|ipf|ipg|ion|ioc|inx|iob|imq|imp|igc|ihp|ihs|ifs|ifp|ifd|iff|iiw|ilb|img|imi|ima|im8|ilg|ilk|kps|kr1|mcd|mcf|mci|mcc|mbx|max|mbk|mcl|mcp|mda|mdb|mdf|md5|md3|mcw|md2|mat|mas|lzs|lzw|lzx|lzh|lzd|lxt|lyr|m3u|m4a|map|mar|man|mak|m7p|mai|mdl|mdm|mib|mid|mii|mht|mgx|mfm|mgf|mio|mis|mks|mkv|ml3|mki|mkg|mix|mke|mff|mfa|mdz|meb|mec|mdx|mdt|mdr|mds|med|mem|meu|mex|met|mes|meq|mer|lxf|lwp|lcs|lcw|ld1|lcn|lcl|lch|lck|lda|ldb|lfd|lfl|lft|lex|lev|ldf|les|lcf|lbx|kyb|la0|lab|ktu|ks3|kr2|krz|lan|lay|lbr|lbt|lbo|lbm|lbg|lbl|lgo|lha|lrx|lsa|lsb|lrs|lrp|lrc|lrf|lsp|lss|lwd|lwo|lua|ltm|lst|lta|lpc|lom|lim|lin|lis|lif|lic|lhw|lib|lit|lix|lod|log|lnk|lng|ll3|lmp|hs2|igf|giw|gkh|gif|fo2|fog|gid|gks|fo1|fnm|glm|fnt|glb|fnx|fol|gib|gfo|gft|fot|gfb|gex|for|gfx|ghs|ghw|fon|gho|fop|fnk|glo|fmo|gp5|fmt|goh|goe|fmv|fmk|gph|gr2|fmb|gpx|gpk|fmf|goc|gmt|gm6|fnd|gly|gls|fni|fn3|gmd|gmp|gms|gml|gmk|gmf|geo|gen|fpw|fpu|fr3|frf|frg|ftx|fpt|fw2|fxd|fxp|fpd|fpm|fw3|ftp|ftm|fsa|frp|frs|frx|frt|fro|frm|fst|fsx|frl|fsm|fsl|fxs|fyf|gcx|gd3|gcf|gcd|gc3|gca|gdf|gdl|hrz|gem|fox|ged|gds|gc1|fp3|g3p|fp5|fp7|g16|fpc|fp4|gam|gbl|gbr|gbc|gba|gb1|gra|gp4|hex|hfi|hep|hdz|hdx|hgl|fh4|hhp|ffi|fft|fh3|hhh|fif|hdw|fio|hcg|hbk|hbe|hbc|hdd|fin|hdr|fig|hdl|fil|hdf|hin|fff|hpj|hpk|hpi|hpg|hpf|hpm|fes|hrf|hrm|fei|hqx|hpp|hp8|hof|hlp|hlx|hlb|hkm|his|hlz|hmi|hnc|hmp|ffe|hmm|fit|hds|grp|gsa|flm|grn|flc|flv|flb|gwi|gs3|gvo|gry|gta|gs1|fli|gup|hap|fld|gxl|grf|fm1|grb|fky|gsm|hal|fix|ham|fm3|flx|grd|gzr|gsd|fla|gsw|gre|flt|flp|ar|mk|fc|rn|md|ff|mm|fm|ng|sd|nc|sh|xi|ai|rc|wn|fd|wp|rs|xx|ms|rh|lz|ws|xy|fp|xp|ri|mf|sc|ad|mu|f4|sf|xa|sb|ap|rf|el|xe|yy|yz|nb|xm|rm|yc|rv|ly|mb|me|fi|ac|mz|z3|bf|gv|io|ip|ui|dd|ps|ce|gz|tz|ty|cf|ha|ub|u8|ul|cd|v8|uw|pb|ca|dh|iw|uu|iv|cc|sw|it|pc|pt|dc|hc|tp|pj|td|id|pp|hi|co|pk|tf|cs|pm|pl|hp|cm|tc|il|tb|ts|im|tv|ch|pg|fe|tr|hh|db|ph|cl|vb|ix|ob|sm|ld|qt|aw|le|g8|b8|so|ks|sn|bb|ba|gb|fx|wd|np|ra|ec|lj|lm|nl|au|sl|nt|dx|fw|lg|r8|ge|sp|vi|br|px|js|gl|ss|pw|dl|di|gp|vc|jk|st|jw|py|bi|dp|qb|vs|vw|ds|q0|vp|bm|vm|kb|vo|bk|rb)\b/i',
				'regex_validate' => '/^[A-Za-z0-9\.\?\-\_]+\.(desklink|numbers|dvdproj|balance|torrent|graffle|package|naplps|sldprt|emaker|adicht|kbasic|udiwww|slddrw|policy|vbproj|fsproj|sldasm|hppcl|plist|spiff|grdnt|vicar|mlraw|proto|ccitt|blend|flame|grasp|pages|sctor|accdu|bpoly|accdt|accde|accda|accdb|dylib|tpoly|saver|class|ffivw|jbig|djvu|jfif|vmdk|gmod|docm|jpeg|vinf|viff|odif|ftxt|fweb|qtvr|objf|ecms|aspx|asmx|nitf|ecmt|smpl|sndb|dotx|kdbx|vrml|olsr|spin|qhcp|pack|soar|docx|cals|php3|php4|cmrl|pict|tddd|cweb|trif|trib|tria|poly|topc|tmy2|cpmz|plsc|tiff|html|plsk|text|hpgl|term|ply2|iges|dats|grib|cats|isma|pseg|fsim|vala|jasc|bufr|par2|part|irtr|uoml|indd|hdmp|pptx|chml|hcgs|info|unif|proj|ingr|java|bdef|face|xlsb|xlsx|sdts|acmb|acis|runz|mdmp|mobi|sdml|sats|film|xspf|m3u8|enff|rtfd|xmpz|xmod|mpkg|wsrc|fits|mpeg|flac|font|midi|mol2|wmdb|shar|rmvb|msqm|safe|saif|msdl|mime|aiff|aifc|miff|prj|pri|prk|rpd|pro|prn|prm|prg|psv|roo|psw|prc|prd|rsy|prf|pre|rpb|prs|psd|psn|psp|rtf|rpm|rpt|rrd|psm|pse|rsc|rpl|psb|prt|psf|prr|rsp|rsl|prx|psq|psa|rpf|pst|ppp|rvw|pmc|pm5|pm4|pm3|pmi|pmm|pn3|pnf|rus|rvf|pmp|rws|ply|pln|plp|plm|rzx|pll|pls|rzk|rwx|plt|rwz|rxd|png|pnm|ppl|ppm|ppk|ppj|ppd|ppo|pt3|pr1|pr2|rtl|ppt|pps|ppb|ppa|run|pop|pol|poh|pnt|rul|rts|pow|pov|rtp|pot|pr3|ptf|qlb|qlc|rev|qiz|qhp|qif|qlp|qm4|res|qrt|qrs|qpx|qpr|rex|rez|qdk|qdr|qdf|qcp|qbw|qch|qdv|qef|rgb|rft|qhc|qfx|rgx|qry|req|rar|rdp|ram|ral|r8p|rad|ras|rat|rbf|raw|rcg|rdf|rdi|rdx|r3d|qwk|rel|rem|qvw|rep|qvd|reg|ref|r2d|rec|qxl|red|qxd|qbo|rib|rlz|rle|put|pud|rmb|pub|rld|pvd|rlb|pvt|rlc|pvm|pvl|ptu|ptr|rol|ptg|ray|ptb|pt4|pt5|ptl|rno|ptm|ptn|rmi|rmk|rnd|pwf|pwk|pzx|q3s|rix|pzt|pzp|pzs|rip|qag|qbe|ric|rif|qbb|qap|rl4|pzo|pxp|rl8|pwp|pwl|rla|pxr|pxz|pzd|pzi|pyo|pyi|pyc|rom|pgl|nk2|nlm|nls|nil|nif|ngo|ngs|nlx|not|nsa|nsf|nss|nrw|nrg|npi|npj|nfo|nff|ncd|ncp|ndb|ncc|nbf|nam|nap|ndl|nds|neu|new|net|nes|ndx|neo|nst|nsv|oct|ocx|odb|ocr|ocf|obz|ocd|odf|odg|odt|ofd|off|ods|odp|odl|odm|obv|obt|nts|ntx|nuf|ntr|nth|nsx|ntf|nws|nxt|obr|obs|obj|obd|o01|oaz|mzx|mzg|mpc|mpg|mpl|mpa|mp4|mp2|mp3|mpm|mpp|mpx|mpz|mrb|mpv|mpt|mpq|mpr|mov|mop|a11|mnd|mng|mmo|mmm|mlv|mmf|mnt|mnu|mol|mon|mod|mob|mnx|mny|mrc|mrk|mvi|mvo|mvw|mvf|muz|mtw|mus|mwf|mws|mxt|myp|mze|mxp|mxm|mxf|mxl|mtv|mts|msi|msn|mso|msg|mse|mrs|msc|msp|mss|mtl|mtm|mth|msx|mst|msw|ofm|ofn|pem|peq|per|ped|peb|pdv|pdw|pes|pet|pf|pfk|pfm|pfs|pfc|pfb|pex|pfa|pds|pdn|pcw|pcx|pda|pct|pcs|pcl|pcm|pdb|pdd|pdl|pdm|pdi|pdg|pde|pdf|pft|pgd|pk3|pk4|pka|pk2|pjx|pix|pjt|pkg|pkt|plb|plc|pld|pla|pl3|pl1|pl2|piv|pit|pgs|phd|phm|pgp|pgm|pgi|s19|phn|pho|pie|pif|pic|pi2|php|phr|pck|pcj|oth|otl|otp|otg|otb|ora|org|ots|ott|ovd|ovl|ovr|ov2|ov1|otx|out|opx|opw|okt|olb|old|oif|ohs|ogg|ohb|oli|oma|opn|opt|opj|oom|oof|oog|oxt|p10|pbl|pbm|pbo|pbk|pbi|pba|pbd|pbt|pc3|pcd|pcf|pch|pcc|pcb|pc8|pca|pb1|pat|p80|p86|pa1|p3t|p22|p12|p16|pac|pad|par|pas|pan|pal|paf|pak|ple|sd7|vmc|vmf|vmg|vlm|vis|vim|vir|vms|vob|vpf|vpg|vph|vox|vop|voc|vof|vik|vif|vdo|vdr|veg|vda|vcx|vcr|vcw|vew|vfa|vgr|vic|vid|vgd|vga|vfm|vfn|vpi|vqa|w3m|w3n|w3o|w3h|w3g|w3b|w3d|w3p|w3q|w3z|w40|w44|w3x|w3v|w3t|w3u|w3a|w31|vsm|vsp|vss|vsd|vrs|vrm|vrp|vst|vue|w01|w30|vzt|vxd|vwr|vwx|vcd|vca|tzl|tzx|u3d|tzb|tym|txt|txw|uc2|ucn|ue2|ufi|uha|udw|udo|ucs|udf|txi|txf|tru|trw|try|trs|trn|trk|trm|tse|tsk|tud|tut|tvf|ttf|tsv|tsm|tst|uhs|uif|v10|v2d|vai|uwf|uue|usr|utz|val|van|vbx|vbz|vc6|vbs|vbr|vap|var|usp|use|umb|unf|uni|ult|ulf|uih|uld|unx|uop|url|urt|upo|upd|uos|uot|w95|wad|xif|xla|xlb|xft|xfr|xex|xfn|xlc|xld|xlt|xlv|xlw|xls|xlm|xlk|xll|xdm|xdf|wtt|wwb|wwk|wtr|wtf|wsq|wst|wxh|wxp|xcf|xck|xcl|xbm|xbe|x3d|xar|xmf|xmi|yrd|yst|yuv|ymp|yal|xyw|xyz|z2s|z3d|zed|zer|zip|zdg|zad|z64|z80|xy3|xxe|xpm|xps|xqt|xof|xnf|xml|xmv|xrf|xsf|xwk|xwp|xwd|xvf|xtb|xtp|wsp|wsd|wfp|wft|wfx|wfn|wfm|wfb|wfd|wg1|wg2|wiz|wk1|wk3|wix|win|wid|wim|web|wdm|wav|wax|wb1|was|war|wag|wai|wb2|wbf|wcm|wcp|wdb|wch|wcd|wbk|wbt|wk4|wkb|wpl|wpm|wps|wpk|wpj|wpf|wpg|wq1|wr1|wrp|wrs|ws2|wrl|wrk|wrd|wri|wpd|wp5|wkz|wlk|wma|wks|wkq|wke|wki|wmc|wmf|woc|wow|woa|wnf|wmp|wmv|tre|trd|sld|sli|slk|slc|slb|skp|sky|sll|slp|smk|sml|smm|smf|smc|slt|slv|sit|sis|shk|shm|shp|shg|shb|sgt|sh3|shr|sht|sig|sik|sim|sif|sib|shw|shx|smn|smp|spw|spx|sqd|spt|sps|spp|spr|sqi|sql|srf|srp|srt|src|sqz|sqm|sqn|spm|spl|sno|snp|sol|sng|snd|sms|smt|som|son|spg|spi|spf|spd|sou|spc|sgp|sgi|scd|scf|sch|scc|sca|sc3|sc4|sci|scm|sct|scx|scy|scr|scp|scn|sco|sbt|sbr|saf|sal|sam|sab|s7i|s3i|s3m|san|sar|sbk|sbl|sbp|sbi|sbd|sat|sav|mli|sda|sff|sfi|sfl|sfd|sfb|set|sf2|sfn|sfo|sfx|sg1|sgf|sft|sfs|sfp|sfr|ses|seq|sdl|sdm|sdn|sdk|sdi|sdc|sdf|sdr|sds|sen|sep|sec|sea|sdw|sdx|ssc|ssd|tes|tet|tex|tem|tel|tec|tef|tfa|tfc|tg1|tga|tgz|tfs|tfm|tff|tfh|tdw|tdt|tch|tcl|tct|tbz|tbx|tbr|tbs|tcw|td0|tdk|tdr|tds|tdh|tdf|td2|tdb|the|thm|tp3|tpb|tpc|tos|tok|tmy|toc|tpf|tph|tpz|tr2|trc|tpw|tpu|tpl|tpp|tms|tmp|til|tis|tjl|tif|tic|ths|tib|tlb|tlc|tmf|tmo|tm2|tlp|tld|tlh|tbl|tbk|sty|sub|suf|stx|stw|sts|stu|sui|sum|svg|svq|svs|svd|sup|sun|suo|str|stp|sst|ssv|sta|ssr|ssp|ssf|ssg|stb|stc|stl|stm|sto|stk|sti|std|stf|svw|svx|t2l|t44|t64|t2b|syw|syn|sys|tag|tah|tb1|tb2|tbf|taz|tar|tal|tap|sym|syg|swt|sxc|sxd|swp|swg|svy|swf|sxg|sxi|sy3|syd|sy1|sxw|sxm|sxp|s1k|mmp|dcp|dcs|dct|dcm|dcl|dch|dci|dcu|dcw|ddp|dds|deb|ddi|ddb|dcx|dda|dcf|dcd|dbg|dbk|dbl|dbf|dbd|dba|dbc|dbm|dbo|dc6|dca|dcc|dbx|dbw|dbs|dbt|def|dem|diz|dkb|dld|dis|dir|dig|dip|dlf|dlg|dmf|dmg|dmo|dls|dlp|dll|dlm|dif|dic|dfi|dfl|dfm|dff|dfd|des|dev|dfs|dfv|dht|dia|dib|dhp|dgs|dfx|dgn|db3|db2|cpc|cpd|cpf|con|com|cof|col|cpi|cpl|cpz|cr2|cra|cpt|cps|cpp|cpr|coe|cod|cmf|cmk|cmm|cmd|cmb|cls|clw|cmp|cms|cnf|cnv|cob|cnd|cnc|cmu|cmv|crd|crf|cvp|cvs|cvt|cv4|cut|cuf|cur|cvw|cwk|daa|daf|dat|d64|d4p|cxx|d4d|ctx|ctt|cru|csd|csg|crt|crs|crk|crp|csm|csp|ctk|ctl|ctf|ctc|css|csv|dmp|dms|epi|eps|eqn|epd|env|end|eng|erd|erl|mlb|etw|etx|etf|esh|erm|err|enc|emu|eft|efv|efx|efs|efq|efe|efk|ega|eka|emd|emf|eml|elt|eli|elb|elc|eui|evt|f96|fac|fam|f95|f90|f4v|f77|faq|far|fdb|fds|fdw|fcm|fbm|fas|fax|f4p|f4b|exe|exf|exm|exc|ex3|evy|ewd|exp|ext|f2r|f3r|f4a|f03|f01|exx|ezf|efa|eeb|dsk|dsm|dsn|dsf|dsd|ds4|dsc|dsp|dsr|dt1|dta|dtb|dsy|dsw|dss|dst|ds1|drw|dog|doh|dor|dof|doc|dng|dnt|dos|dot|drp|drs|drv|dpx|dpr|dox|doz|dtf|dtl|ecc|ece|ed5|ebq|ebj|eas|eba|eda|ede|eds|edt|edv|edq|edl|edf|edk|ear|e2d|dvi|dvp|dw2|dvc|dtp|dtm|dto|dwb|dwc|dxp|dyn|dxn|dxf|dwd|dwg|clr|clp|arj|ark|arr|ari|arh|ard|arf|art|arx|ash|asi|ask|asf|ase|asc|asd|arc|ar7|aos|apc|apd|aol|ans|anm|ann|ape|apf|apr|apw|apx|app|apm|api|apl|asm|aso|awa|awd|awk|avt|avs|avi|avr|awm|aws|azz|b1n|b30|azw|azd|axf|axm|avb|ava|asx|asz|at2|asw|ast|asp|ass|at3|atm|aup|aux|auz|aud|aty|atn|att|ani|ana|ad2|ad3|ada|act|aco|acl|acm|adb|adc|adl|adm|adn|adi|adg|add|adf|acf|ace|ab6|ab8|abc|aas|aaf|a80|aac|abf|abk|aca|acb|acc|abs|abr|abm|abp|adr|ads|ale|alg|all|alc|aix|aio|ais|alo|alt|aml|amr|ams|amg|amf|alz|amd|ain|aif|aep|af2|af3|adz|adx|adt|adv|afi|afl|agr|ahk|afw|aft|afm|afp|bac|bad|cbz|ccc|ccf|cbt|cbs|cbm|cbr|cch|ccl|cdf|cdk|cdl|cdb|cda|cco|cct|cbl|cbc|c01|c86|cab|c00|bzm|byu|bz2|cac|cad|cas|cat|cba|cap|can|cal|cam|cdm|cdr|chl|chm|chn|chk|chi|ch4|chd|chp|chr|ckb|cla|cld|cix|cif|cht|chz|ch3|cgm|ceg|cel|cfg|cef|ceb|cdt|cdx|cfl|cfm|cga|cgi|cft|cfp|cfn|cfo|bwr|bwb|bf2|bfc|bfm|bez|bdt|bdm|bdr|bfx|bga|bin|bio|bit|bik|bif|bgi|bib|bdf|bdc|bat|bbl|bbm|bas|bar|bak|bal|bbp|bbs|bct|bcw|bdb|bcp|bco|bcf|bch|bkp|bkw|bsc|bsp|bss|bsa|bs2|brk|brl|bst|bsy|bup|but|buy|bun|bug|btm|btn|brd|bpt|bmk|bmp|bmt|bm3|blk|bld|blg|bna|bnk|bpc|bpp|bot|boo|bnr|bok|feb|eth|izt|j2c|jam|iwp|iwd|ivu|iwa|jar|jas|jet|jff|jif|jcn|jbx|jav|jbd|ivp|ivl|irt|isd|ish|irs|ipt|ipn|ips|iso|ist|ive|ivi|itt|itn|itf|itl|jms|job|kep|kex|key|kdc|kdb|kbm|kcl|kfx|kit|kno|kpl|kpp|kmz|kmy|kmc|kml|kbd|kar|jpg|jre|jrn|jpc|jp2|jor|jou|jsp|jtf|k3d|kap|k2a|jzz|jvx|jwl|ipl|iph|ica|icb|icc|ibm|ibg|iam|iax|ice|icm|ide|idf|ids|idc|idb|icn|ico|iak|hyp|htm|htx|hwd|hst|hsi|hsc|hsh|hwp|hxm|hyc|hyd|hy2|hy1|hxs|hxx|idw|idx|inf|ini|ink|ind|inc|in3|inb|ins|int|ipc|ipf|ipg|ion|ioc|inx|iob|imq|imp|igc|ihp|ihs|ifs|ifp|ifd|iff|iiw|ilb|img|imi|ima|im8|ilg|ilk|kps|kr1|mcd|mcf|mci|mcc|mbx|max|mbk|mcl|mcp|mda|mdb|mdf|md5|md3|mcw|md2|mat|mas|lzs|lzw|lzx|lzh|lzd|lxt|lyr|m3u|m4a|map|mar|man|mak|m7p|mai|mdl|mdm|mib|mid|mii|mht|mgx|mfm|mgf|mio|mis|mks|mkv|ml3|mki|mkg|mix|mke|mff|mfa|mdz|meb|mec|mdx|mdt|mdr|mds|med|mem|meu|mex|met|mes|meq|mer|lxf|lwp|lcs|lcw|ld1|lcn|lcl|lch|lck|lda|ldb|lfd|lfl|lft|lex|lev|ldf|les|lcf|lbx|kyb|la0|lab|ktu|ks3|kr2|krz|lan|lay|lbr|lbt|lbo|lbm|lbg|lbl|lgo|lha|lrx|lsa|lsb|lrs|lrp|lrc|lrf|lsp|lss|lwd|lwo|lua|ltm|lst|lta|lpc|lom|lim|lin|lis|lif|lic|lhw|lib|lit|lix|lod|log|lnk|lng|ll3|lmp|hs2|igf|giw|gkh|gif|fo2|fog|gid|gks|fo1|fnm|glm|fnt|glb|fnx|fol|gib|gfo|gft|fot|gfb|gex|for|gfx|ghs|ghw|fon|gho|fop|fnk|glo|fmo|gp5|fmt|goh|goe|fmv|fmk|gph|gr2|fmb|gpx|gpk|fmf|goc|gmt|gm6|fnd|gly|gls|fni|fn3|gmd|gmp|gms|gml|gmk|gmf|geo|gen|fpw|fpu|fr3|frf|frg|ftx|fpt|fw2|fxd|fxp|fpd|fpm|fw3|ftp|ftm|fsa|frp|frs|frx|frt|fro|frm|fst|fsx|frl|fsm|fsl|fxs|fyf|gcx|gd3|gcf|gcd|gc3|gca|gdf|gdl|hrz|gem|fox|ged|gds|gc1|fp3|g3p|fp5|fp7|g16|fpc|fp4|gam|gbl|gbr|gbc|gba|gb1|gra|gp4|hex|hfi|hep|hdz|hdx|hgl|fh4|hhp|ffi|fft|fh3|hhh|fif|hdw|fio|hcg|hbk|hbe|hbc|hdd|fin|hdr|fig|hdl|fil|hdf|hin|fff|hpj|hpk|hpi|hpg|hpf|hpm|fes|hrf|hrm|fei|hqx|hpp|hp8|hof|hlp|hlx|hlb|hkm|his|hlz|hmi|hnc|hmp|ffe|hmm|fit|hds|grp|gsa|flm|grn|flc|flv|flb|gwi|gs3|gvo|gry|gta|gs1|fli|gup|hap|fld|gxl|grf|fm1|grb|fky|gsm|hal|fix|ham|fm3|flx|grd|gzr|gsd|fla|gsw|gre|flt|flp|ar|mk|fc|rn|md|ff|mm|fm|ng|sd|nc|sh|xi|ai|rc|wn|fd|wp|rs|xx|ms|rh|lz|ws|xy|fp|xp|ri|mf|sc|ad|mu|f4|sf|xa|sb|ap|rf|el|xe|yy|yz|nb|xm|rm|yc|rv|ly|mb|me|fi|ac|mz|z3|bf|gv|io|ip|ui|dd|ps|ce|gz|tz|ty|cf|ha|ub|u8|ul|cd|v8|uw|pb|ca|dh|iw|uu|iv|cc|sw|it|pc|pt|dc|hc|tp|pj|td|id|pp|hi|co|pk|tf|cs|pm|pl|hp|cm|tc|il|tb|ts|im|tv|ch|pg|fe|tr|hh|db|ph|cl|vb|ix|ob|sm|ld|qt|aw|le|g8|b8|so|ks|sn|bb|ba|gb|fx|wd|np|ra|ec|lj|lm|nl|au|sl|nt|dx|fw|lg|r8|ge|sp|vi|br|px|js|gl|ss|pw|dl|di|gp|vc|jk|st|jw|py|bi|dp|qb|vs|vw|ds|q0|vp|bm|vm|kb|vo|bk|rb)$/i',
				'remove_string' => false,
				'whitelist' => array(
					'#^\.\.+#i',
					'/@+/i',
					'/(gov|org|com|edu|info|lang|titl|writ|net)$/i',
					'/^www\./i',
					'/\?+/i',
				),
				'obfuscate' => array(
					'.' => '[.]',
				),
			),
			'filepath' => array(
				'regex' => '/([a-z0-9]\:)?\\\[\w+ \<\>\%\\\\~]+\.(desklink|numbers|dvdproj|balance|torrent|graffle|package|naplps|sldprt|emaker|adicht|kbasic|udiwww|slddrw|policy|vbproj|fsproj|sldasm|hppcl|plist|spiff|grdnt|vicar|mlraw|proto|ccitt|blend|flame|grasp|pages|sctor|accdu|bpoly|accdt|accde|accda|accdb|dylib|tpoly|saver|class|ffivw|jbig|djvu|jfif|vmdk|gmod|docm|jpeg|vinf|viff|odif|ftxt|fweb|qtvr|objf|ecms|aspx|asmx|nitf|ecmt|smpl|sndb|dotx|kdbx|vrml|olsr|spin|qhcp|pack|soar|docx|cals|php3|php4|cmrl|pict|tddd|cweb|trif|trib|tria|poly|topc|tmy2|cpmz|plsc|tiff|html|plsk|text|hpgl|term|ply2|iges|dats|grib|cats|isma|pseg|fsim|vala|jasc|bufr|par2|part|irtr|uoml|indd|hdmp|pptx|chml|hcgs|info|unif|proj|ingr|java|bdef|face|xlsb|xlsx|sdts|acmb|acis|runz|mdmp|mobi|sdml|sats|film|xspf|m3u8|enff|rtfd|xmpz|xmod|mpkg|wsrc|fits|mpeg|flac|font|midi|mol2|wmdb|shar|rmvb|msqm|safe|saif|msdl|mime|aiff|aifc|miff|prj|pri|prk|rpd|pro|prn|prm|prg|psv|roo|psw|prc|prd|rsy|prf|pre|rpb|prs|psd|psn|psp|rtf|rpm|rpt|rrd|psm|pse|rsc|rpl|psb|prt|psf|prr|rsp|rsl|prx|psq|psa|rpf|pst|ppp|rvw|pmc|pm5|pm4|pm3|pmi|pmm|pn3|pnf|rus|rvf|pmp|rws|ply|pln|plp|plm|rzx|pll|pls|rzk|rwx|plt|rwz|rxd|png|pnm|ppl|ppm|ppk|ppj|ppd|ppo|pt3|pr1|pr2|rtl|ppt|pps|ppb|ppa|run|pop|pol|poh|pnt|rul|rts|pow|pov|rtp|pot|pr3|ptf|qlb|qlc|rev|qiz|qhp|qif|qlp|qm4|res|qrt|qrs|qpx|qpr|rex|rez|qdk|qdr|qdf|qcp|qbw|qch|qdv|qef|rgb|rft|qhc|qfx|rgx|qry|req|rar|rdp|ram|ral|r8p|rad|ras|rat|rbf|raw|rcg|rdf|rdi|rdx|r3d|qwk|rel|rem|qvw|rep|qvd|reg|ref|r2d|rec|qxl|red|qxd|qbo|rib|rlz|rle|put|pud|rmb|pub|rld|pvd|rlb|pvt|rlc|pvm|pvl|ptu|ptr|rol|ptg|ray|ptb|pt4|pt5|ptl|rno|ptm|ptn|rmi|rmk|rnd|pwf|pwk|pzx|q3s|rix|pzt|pzp|pzs|rip|qag|qbe|ric|rif|qbb|qap|rl4|pzo|pxp|rl8|pwp|pwl|rla|pxr|pxz|pzd|pzi|pyo|pyi|pyc|rom|pgl|nk2|nlm|nls|nil|nif|ngo|ngs|nlx|not|nsa|nsf|nss|nrw|nrg|npi|npj|nfo|nff|ncd|ncp|ndb|ncc|nbf|nam|nap|ndl|nds|neu|new|net|nes|ndx|neo|nst|nsv|oct|ocx|odb|ocr|ocf|obz|ocd|odf|odg|odt|ofd|off|ods|odp|odl|odm|obv|obt|nts|ntx|nuf|ntr|nth|nsx|ntf|nws|nxt|obr|obs|obj|obd|o01|oaz|mzx|mzg|mpc|mpg|mpl|mpa|mp4|mp2|mp3|mpm|mpp|mpx|mpz|mrb|mpv|mpt|mpq|mpr|mov|mop|a11|mnd|mng|mmo|mmm|mlv|mmf|mnt|mnu|mol|mon|mod|mob|mnx|mny|mrc|mrk|mvi|mvo|mvw|mvf|muz|mtw|mus|mwf|mws|mxt|myp|mze|mxp|mxm|mxf|mxl|mtv|mts|msi|msn|mso|msg|mse|mrs|msc|msp|mss|mtl|mtm|mth|msx|mst|msw|ofm|ofn|pem|peq|per|ped|peb|pdv|pdw|pes|pet|pfk|pfm|pfs|pfc|pfb|pex|pfa|pds|pdn|pcw|pcx|pda|pct|pcs|pcl|pcm|pdb|pdd|pdl|pdm|pdi|pdg|pde|pdf|pft|pgd|pk3|pk4|pka|pk2|pjx|pix|pjt|pkg|pkt|plb|plc|pld|pla|pl3|pl1|pl2|piv|pit|pgs|phd|phm|pgp|pgm|pgi|s19|phn|pho|pie|pif|pic|pi2|php|phr|pck|pcj|oth|otl|otp|otg|otb|ora|org|ots|ott|ovd|ovl|ovr|ov2|ov1|otx|out|opx|opw|okt|olb|old|oif|ohs|ogg|ohb|oli|oma|opn|opt|opj|oom|oof|oog|oxt|p10|pbl|pbm|pbo|pbk|pbi|pba|pbd|pbt|pc3|pcd|pcf|pch|pcc|pcb|pc8|pca|pb1|pat|p80|p86|pa1|p3t|p22|p12|p16|pac|pad|par|pas|pan|pal|paf|pak|ple|sd7|vmc|vmf|vmg|vlm|vis|vim|vir|vms|vob|vpf|vpg|vph|vox|vop|voc|vof|vik|vif|vdo|vdr|veg|vda|vcx|vcr|vcw|vew|vfa|vgr|vic|vid|vgd|vga|vfm|vfn|vpi|vqa|w3m|w3n|w3o|w3h|w3g|w3b|w3d|w3p|w3q|w3z|w40|w44|w3x|w3v|w3t|w3u|w3a|w31|vsm|vsp|vss|vsd|vrs|vrm|vrp|vst|vue|w01|w30|vzt|vxd|vwr|vwx|vcd|vca|tzl|tzx|u3d|tzb|tym|txt|txw|uc2|ucn|ue2|ufi|uha|udw|udo|ucs|udf|txi|txf|tru|trw|try|trs|trn|trk|trm|tse|tsk|tud|tut|tvf|ttf|tsv|tsm|tst|uhs|uif|v10|v2d|vai|uwf|uue|usr|utz|val|van|vbx|vbz|vc6|vbs|vbr|vap|var|usp|use|umb|unf|uni|ult|ulf|uih|uld|unx|uop|url|urt|upo|upd|uos|uot|w95|wad|xif|xla|xlb|xft|xfr|xex|xfn|xlc|xld|xlt|xlv|xlw|xls|xlm|xlk|xll|xdm|xdf|wtt|wwb|wwk|wtr|wtf|wsq|wst|wxh|wxp|xcf|xck|xcl|xbm|xbe|x3d|xar|xmf|xmi|yrd|yst|yuv|ymp|yal|xyw|xyz|z2s|z3d|zed|zer|zip|zdg|zad|z64|z80|xy3|xxe|xpm|xps|xqt|xof|xnf|xml|xmv|xrf|xsf|xwk|xwp|xwd|xvf|xtb|xtp|wsp|wsd|wfp|wft|wfx|wfn|wfm|wfb|wfd|wg1|wg2|wiz|wk1|wk3|wix|win|wid|wim|web|wdm|wav|wax|wb1|was|war|wag|wai|wb2|wbf|wcm|wcp|wdb|wch|wcd|wbk|wbt|wk4|wkb|wpl|wpm|wps|wpk|wpj|wpf|wpg|wq1|wr1|wrp|wrs|ws2|wrl|wrk|wrd|wri|wpd|wp5|wkz|wlk|wma|wks|wkq|wke|wki|wmc|wmf|woc|wow|woa|wnf|wmp|wmv|tre|trd|sld|sli|slk|slc|slb|skp|sky|sll|slp|smk|sml|smm|smf|smc|slt|slv|sit|sis|shk|shm|shp|shg|shb|sgt|sh3|shr|sht|sig|sik|sim|sif|sib|shw|shx|smn|smp|spw|spx|sqd|spt|sps|spp|spr|sqi|sql|srf|srp|srt|src|sqz|sqm|sqn|spm|spl|sno|snp|sol|sng|snd|sms|smt|som|son|spg|spi|spf|spd|sou|spc|sgp|sgi|scd|scf|sch|scc|sca|sc3|sc4|sci|scm|sct|scx|scy|scr|scp|scn|sco|sbt|sbr|saf|sal|sam|sab|s7i|s3i|s3m|san|sar|sbk|sbl|sbp|sbi|sbd|sat|sav|mli|sda|sff|sfi|sfl|sfd|sfb|set|sf2|sfn|sfo|sfx|sg1|sgf|sft|sfs|sfp|sfr|ses|seq|sdl|sdm|sdn|sdk|sdi|sdc|sdf|sdr|sds|sen|sep|sec|sea|sdw|sdx|ssc|ssd|tes|tet|tex|tem|tel|tec|tef|tfa|tfc|tg1|tga|tgz|tfs|tfm|tff|tfh|tdw|tdt|tch|tcl|tct|tbz|tbx|tbr|tbs|tcw|td0|tdk|tdr|tds|tdh|tdf|td2|tdb|the|thm|tp3|tpb|tpc|tos|tok|tmy|toc|tpf|tph|tpz|tr2|trc|tpw|tpu|tpl|tpp|tms|tmp|til|tis|tjl|tif|tic|ths|tib|tlb|tlc|tmf|tmo|tm2|tlp|tld|tlh|tbl|tbk|sty|sub|suf|stx|stw|sts|stu|sui|sum|svg|svq|svs|svd|sup|sun|suo|str|stp|sst|ssv|sta|ssr|ssp|ssf|ssg|stb|stc|stl|stm|sto|stk|sti|std|stf|svw|svx|t2l|t44|t64|t2b|syw|syn|sys|tag|tah|tb1|tb2|tbf|taz|tar|tal|tap|sym|syg|swt|sxc|sxd|swp|swg|svy|swf|sxg|sxi|sy3|syd|sy1|sxw|sxm|sxp|s1k|mmp|dcp|dcs|dct|dcm|dcl|dch|dci|dcu|dcw|ddp|dds|deb|ddi|ddb|dcx|dda|dcf|dcd|dbg|dbk|dbl|dbf|dbd|dba|dbc|dbm|dbo|dc6|dca|dcc|dbx|dbw|dbs|dbt|def|dem|diz|dkb|dld|dis|dir|dig|dip|dlf|dlg|dmf|dmg|dmo|dls|dlp|dll|dlm|dif|dic|dfi|dfl|dfm|dff|dfd|des|dev|dfs|dfv|dht|dia|dib|dhp|dgs|dfx|dgn|db3|db2|cpc|cpd|cpf|con|com|cof|col|cpi|cpl|cpz|cr2|cra|cpt|cps|cpp|cpr|coe|cod|cmf|cmk|cmm|cmd|cmb|cls|clw|cmp|cms|cnf|cnv|cob|cnd|cnc|cmu|cmv|crd|crf|cvp|cvs|cvt|cv4|cut|cuf|cur|cvw|cwk|daa|daf|dat|d64|d4p|cxx|d4d|ctx|ctt|cru|csd|csg|crt|crs|crk|crp|csm|csp|ctk|ctl|ctf|ctc|css|csv|dmp|dms|epi|eps|eqn|epd|env|end|eng|erd|erl|mlb|etw|etx|etf|esh|erm|err|enc|emu|eft|efv|efx|efs|efq|efe|efk|ega|eka|emd|emf|eml|elt|eli|elb|elc|eui|evt|f96|fac|fam|f95|f90|f4v|f77|faq|far|fdb|fds|fdw|fcm|fbm|fas|fax|f4p|f4b|exe|exf|exm|exc|ex3|evy|ewd|exp|ext|f2r|f3r|f4a|f03|f01|exx|ezf|efa|eeb|dsk|dsm|dsn|dsf|dsd|ds4|dsc|dsp|dsr|dt1|dta|dtb|dsy|dsw|dss|dst|ds1|drw|dog|doh|dor|dof|doc|dng|dnt|dos|dot|drp|drs|drv|dpx|dpr|dox|doz|dtf|dtl|ecc|ece|ed5|ebq|ebj|eas|eba|eda|ede|eds|edt|edv|edq|edl|edf|edk|ear|e2d|dvi|dvp|dw2|dvc|dtp|dtm|dto|dwb|dwc|dxp|dyn|dxn|dxf|dwd|dwg|clr|clp|arj|ark|arr|ari|arh|ard|arf|art|arx|ash|asi|ask|asf|ase|asc|asd|arc|ar7|aos|apc|apd|aol|ans|anm|ann|ape|apf|apr|apw|apx|app|apm|api|apl|asm|aso|awa|awd|awk|avt|avs|avi|avr|awm|aws|azz|b1n|b30|azw|azd|axf|axm|avb|ava|asx|asz|at2|asw|ast|asp|ass|at3|atm|aup|aux|auz|aud|aty|atn|att|ani|ana|ad2|ad3|ada|act|aco|acl|acm|adb|adc|adl|adm|adn|adi|adg|add|adf|acf|ace|ab6|ab8|abc|aas|aaf|a80|aac|abf|abk|aca|acb|acc|abs|abr|abm|abp|adr|ads|ale|alg|all|alc|aix|aio|ais|alo|alt|aml|amr|ams|amg|amf|alz|amd|ain|aif|aep|af2|af3|adz|adx|adt|adv|afi|afl|agr|ahk|afw|aft|afm|afp|bac|bad|cbz|ccc|ccf|cbt|cbs|cbm|cbr|cch|ccl|cdf|cdk|cdl|cdb|cda|cco|cct|cbl|cbc|c01|c86|cab|c00|bzm|byu|bz2|cac|cad|cas|cat|cba|cap|can|cal|cam|cdm|cdr|chl|chm|chn|chk|chi|ch4|chd|chp|chr|ckb|cla|cld|cix|cif|cht|chz|ch3|cgm|ceg|cel|cfg|cef|ceb|cdt|cdx|cfl|cfm|cga|cgi|cft|cfp|cfn|cfo|bwr|bwb|bf2|bfc|bfm|bez|bdt|bdm|bdr|bfx|bga|bin|bio|bit|bik|bif|bgi|bib|bdf|bdc|bat|bbl|bbm|bas|bar|bak|bal|bbp|bbs|bct|bcw|bdb|bcp|bco|bcf|bch|bkp|bkw|bsc|bsp|bss|bsa|bs2|brk|brl|bst|bsy|bup|but|buy|bun|bug|btm|btn|brd|bpt|bmk|bmp|bmt|bm3|blk|bld|blg|bna|bnk|bpc|bpp|bot|boo|bnr|bok|feb|eth|izt|j2c|jam|iwp|iwd|ivu|iwa|jar|jas|jet|jff|jif|jcn|jbx|jav|jbd|ivp|ivl|irt|isd|ish|irs|ipt|ipn|ips|iso|ist|ive|ivi|itt|itn|itf|itl|jms|job|kep|kex|key|kdc|kdb|kbm|kcl|kfx|kit|kno|kpl|kpp|kmz|kmy|kmc|kml|kbd|kar|jpg|jre|jrn|jpc|jp2|jor|jou|jsp|jtf|k3d|kap|k2a|jzz|jvx|jwl|ipl|iph|ica|icb|icc|ibm|ibg|iam|iax|ice|icm|ide|idf|ids|idc|idb|icn|ico|iak|hyp|htm|htx|hwd|hst|hsi|hsc|hsh|hwp|hxm|hyc|hyd|hy2|hy1|hxs|hxx|idw|idx|inf|ini|ink|ind|inc|in3|inb|ins|int|ipc|ipf|ipg|ion|ioc|inx|iob|imq|imp|igc|ihp|ihs|ifs|ifp|ifd|iff|iiw|ilb|img|imi|ima|im8|ilg|ilk|kps|kr1|mcd|mcf|mci|mcc|mbx|max|mbk|mcl|mcp|mda|mdb|mdf|md5|md3|mcw|md2|mat|mas|lzs|lzw|lzx|lzh|lzd|lxt|lyr|m3u|m4a|map|mar|man|mak|m7p|mai|mdl|mdm|mib|mid|mii|mht|mgx|mfm|mgf|mio|mis|mks|mkv|ml3|mki|mkg|mix|mke|mff|mfa|mdz|meb|mec|mdx|mdt|mdr|mds|med|mem|meu|mex|met|mes|meq|mer|lxf|lwp|lcs|lcw|ld1|lcn|lcl|lch|lck|lda|ldb|lfd|lfl|lft|lex|lev|ldf|les|lcf|lbx|kyb|la0|lab|ktu|ks3|kr2|krz|lan|lay|lbr|lbt|lbo|lbm|lbg|lbl|lgo|lha|lrx|lsa|lsb|lrs|lrp|lrc|lrf|lsp|lss|lwd|lwo|lua|ltm|lst|lta|lpc|lom|lim|lin|lis|lif|lic|lhw|lib|lit|lix|lod|log|lnk|lng|ll3|lmp|hs2|igf|giw|gkh|gif|fo2|fog|gid|gks|fo1|fnm|glm|fnt|glb|fnx|fol|gib|gfo|gft|fot|gfb|gex|for|gfx|ghs|ghw|fon|gho|fop|fnk|glo|fmo|gp5|fmt|goh|goe|fmv|fmk|gph|gr2|fmb|gpx|gpk|fmf|goc|gmt|gm6|fnd|gly|gls|fni|fn3|gmd|gmp|gms|gml|gmk|gmf|geo|gen|fpw|fpu|fr3|frf|frg|ftx|fpt|fw2|fxd|fxp|fpd|fpm|fw3|ftp|ftm|fsa|frp|frs|frx|frt|fro|frm|fst|fsx|frl|fsm|fsl|fxs|fyf|gcx|gd3|gcf|gcd|gc3|gca|gdf|gdl|hrz|gem|fox|ged|gds|gc1|fp3|g3p|fp5|fp7|g16|fpc|fp4|gam|gbl|gbr|gbc|gba|gb1|gra|gp4|hex|hfi|hep|hdz|hdx|hgl|fh4|hhp|ffi|fft|fh3|hhh|fif|hdw|fio|hcg|hbk|hbe|hbc|hdd|fin|hdr|fig|hdl|fil|hdf|hin|fff|hpj|hpk|hpi|hpg|hpf|hpm|fes|hrf|hrm|fei|hqx|hpp|hp8|hof|hlp|hlx|hlb|hkm|his|hlz|hmi|hnc|hmp|ffe|hmm|fit|hds|grp|gsa|flm|grn|flc|flv|flb|gwi|gs3|gvo|gry|gta|gs1|fli|gup|hap|fld|gxl|grf|fm1|grb|fky|gsm|hal|fix|ham|fm3|flx|grd|gzr|gsd|fla|gsw|gre|flt|flp|ar|mk|fc|rn|md|ff|mm|fm|ng|sd|nc|sh|xi|ai|rc|wn|fd|wp|rs|xx|ms|rh|lz|ws|xy|fp|xp|ri|mf|sc|ad|mu|f4|sf|xa|sb|ap|rf|el|xe|yy|yz|nb|xm|rm|yc|rv|ly|mb|me|fi|ac|mz|z3|bf|gv|io|ip|ui|dd|ps|ce|gz|tz|ty|cf|ha|ub|u8|ul|cd|v8|uw|pb|ca|dh|iw|uu|iv|cc|sw|it|pc|pt|dc|hc|tp|pj|td|id|pp|hi|co|pk|tf|cs|pm|pl|hp|cm|tc|il|tb|ts|im|tv|ch|pg|fe|tr|hh|db|ph|cl|vb|ix|ob|sm|ld|qt|aw|le|g8|b8|so|ks|sn|bb|ba|gb|fx|wd|np|ra|ec|lj|lm|nl|au|sl|nt|dx|fw|lg|r8|ge|sp|vi|br|px|js|gl|ss|pw|dl|di|gp|vc|jk|st|jw|py|bi|dp|qb|vs|vw|ds|q0|vp|bm|vm|kb|vo|bk|rb)/i',
				'regex_validate' => '/^([a-z0-9]\:)?\\\[\w+ \<\>\%\\\\~]+\.(desklink|numbers|dvdproj|balance|torrent|graffle|package|naplps|sldprt|emaker|adicht|kbasic|udiwww|slddrw|policy|vbproj|fsproj|sldasm|hppcl|plist|spiff|grdnt|vicar|mlraw|proto|ccitt|blend|flame|grasp|pages|sctor|accdu|bpoly|accdt|accde|accda|accdb|dylib|tpoly|saver|class|ffivw|jbig|djvu|jfif|vmdk|gmod|docm|jpeg|vinf|viff|odif|ftxt|fweb|qtvr|objf|ecms|aspx|asmx|nitf|ecmt|smpl|sndb|dotx|kdbx|vrml|olsr|spin|qhcp|pack|soar|docx|cals|php3|php4|cmrl|pict|tddd|cweb|trif|trib|tria|poly|topc|tmy2|cpmz|plsc|tiff|html|plsk|text|hpgl|term|ply2|iges|dats|grib|cats|isma|pseg|fsim|vala|jasc|bufr|par2|part|irtr|uoml|indd|hdmp|pptx|chml|hcgs|info|unif|proj|ingr|java|bdef|face|xlsb|xlsx|sdts|acmb|acis|runz|mdmp|mobi|sdml|sats|film|xspf|m3u8|enff|rtfd|xmpz|xmod|mpkg|wsrc|fits|mpeg|flac|font|midi|mol2|wmdb|shar|rmvb|msqm|safe|saif|msdl|mime|aiff|aifc|miff|prj|pri|prk|rpd|pro|prn|prm|prg|psv|roo|psw|prc|prd|rsy|prf|pre|rpb|prs|psd|psn|psp|rtf|rpm|rpt|rrd|psm|pse|rsc|rpl|psb|prt|psf|prr|rsp|rsl|prx|psq|psa|rpf|pst|ppp|rvw|pmc|pm5|pm4|pm3|pmi|pmm|pn3|pnf|rus|rvf|pmp|rws|ply|pln|plp|plm|rzx|pll|pls|rzk|rwx|plt|rwz|rxd|png|pnm|ppl|ppm|ppk|ppj|ppd|ppo|pt3|pr1|pr2|rtl|ppt|pps|ppb|ppa|run|pop|pol|poh|pnt|rul|rts|pow|pov|rtp|pot|pr3|ptf|qlb|qlc|rev|qiz|qhp|qif|qlp|qm4|res|qrt|qrs|qpx|qpr|rex|rez|qdk|qdr|qdf|qcp|qbw|qch|qdv|qef|rgb|rft|qhc|qfx|rgx|qry|req|rar|rdp|ram|ral|r8p|rad|ras|rat|rbf|raw|rcg|rdf|rdi|rdx|r3d|qwk|rel|rem|qvw|rep|qvd|reg|ref|r2d|rec|qxl|red|qxd|qbo|rib|rlz|rle|put|pud|rmb|pub|rld|pvd|rlb|pvt|rlc|pvm|pvl|ptu|ptr|rol|ptg|ray|ptb|pt4|pt5|ptl|rno|ptm|ptn|rmi|rmk|rnd|pwf|pwk|pzx|q3s|rix|pzt|pzp|pzs|rip|qag|qbe|ric|rif|qbb|qap|rl4|pzo|pxp|rl8|pwp|pwl|rla|pxr|pxz|pzd|pzi|pyo|pyi|pyc|rom|pgl|nk2|nlm|nls|nil|nif|ngo|ngs|nlx|not|nsa|nsf|nss|nrw|nrg|npi|npj|nfo|nff|ncd|ncp|ndb|ncc|nbf|nam|nap|ndl|nds|neu|new|net|nes|ndx|neo|nst|nsv|oct|ocx|odb|ocr|ocf|obz|ocd|odf|odg|odt|ofd|off|ods|odp|odl|odm|obv|obt|nts|ntx|nuf|ntr|nth|nsx|ntf|nws|nxt|obr|obs|obj|obd|o01|oaz|mzx|mzg|mpc|mpg|mpl|mpa|mp4|mp2|mp3|mpm|mpp|mpx|mpz|mrb|mpv|mpt|mpq|mpr|mov|mop|a11|mnd|mng|mmo|mmm|mlv|mmf|mnt|mnu|mol|mon|mod|mob|mnx|mny|mrc|mrk|mvi|mvo|mvw|mvf|muz|mtw|mus|mwf|mws|mxt|myp|mze|mxp|mxm|mxf|mxl|mtv|mts|msi|msn|mso|msg|mse|mrs|msc|msp|mss|mtl|mtm|mth|msx|mst|msw|ofm|ofn|pem|peq|per|ped|peb|pdv|pdw|pes|pet|pfk|pfm|pfs|pfc|pfb|pex|pfa|pds|pdn|pcw|pcx|pda|pct|pcs|pcl|pcm|pdb|pdd|pdl|pdm|pdi|pdg|pde|pdf|pft|pgd|pk3|pk4|pka|pk2|pjx|pix|pjt|pkg|pkt|plb|plc|pld|pla|pl3|pl1|pl2|piv|pit|pgs|phd|phm|pgp|pgm|pgi|s19|phn|pho|pie|pif|pic|pi2|php|phr|pck|pcj|oth|otl|otp|otg|otb|ora|org|ots|ott|ovd|ovl|ovr|ov2|ov1|otx|out|opx|opw|okt|olb|old|oif|ohs|ogg|ohb|oli|oma|opn|opt|opj|oom|oof|oog|oxt|p10|pbl|pbm|pbo|pbk|pbi|pba|pbd|pbt|pc3|pcd|pcf|pch|pcc|pcb|pc8|pca|pb1|pat|p80|p86|pa1|p3t|p22|p12|p16|pac|pad|par|pas|pan|pal|paf|pak|ple|sd7|vmc|vmf|vmg|vlm|vis|vim|vir|vms|vob|vpf|vpg|vph|vox|vop|voc|vof|vik|vif|vdo|vdr|veg|vda|vcx|vcr|vcw|vew|vfa|vgr|vic|vid|vgd|vga|vfm|vfn|vpi|vqa|w3m|w3n|w3o|w3h|w3g|w3b|w3d|w3p|w3q|w3z|w40|w44|w3x|w3v|w3t|w3u|w3a|w31|vsm|vsp|vss|vsd|vrs|vrm|vrp|vst|vue|w01|w30|vzt|vxd|vwr|vwx|vcd|vca|tzl|tzx|u3d|tzb|tym|txt|txw|uc2|ucn|ue2|ufi|uha|udw|udo|ucs|udf|txi|txf|tru|trw|try|trs|trn|trk|trm|tse|tsk|tud|tut|tvf|ttf|tsv|tsm|tst|uhs|uif|v10|v2d|vai|uwf|uue|usr|utz|val|van|vbx|vbz|vc6|vbs|vbr|vap|var|usp|use|umb|unf|uni|ult|ulf|uih|uld|unx|uop|url|urt|upo|upd|uos|uot|w95|wad|xif|xla|xlb|xft|xfr|xex|xfn|xlc|xld|xlt|xlv|xlw|xls|xlm|xlk|xll|xdm|xdf|wtt|wwb|wwk|wtr|wtf|wsq|wst|wxh|wxp|xcf|xck|xcl|xbm|xbe|x3d|xar|xmf|xmi|yrd|yst|yuv|ymp|yal|xyw|xyz|z2s|z3d|zed|zer|zip|zdg|zad|z64|z80|xy3|xxe|xpm|xps|xqt|xof|xnf|xml|xmv|xrf|xsf|xwk|xwp|xwd|xvf|xtb|xtp|wsp|wsd|wfp|wft|wfx|wfn|wfm|wfb|wfd|wg1|wg2|wiz|wk1|wk3|wix|win|wid|wim|web|wdm|wav|wax|wb1|was|war|wag|wai|wb2|wbf|wcm|wcp|wdb|wch|wcd|wbk|wbt|wk4|wkb|wpl|wpm|wps|wpk|wpj|wpf|wpg|wq1|wr1|wrp|wrs|ws2|wrl|wrk|wrd|wri|wpd|wp5|wkz|wlk|wma|wks|wkq|wke|wki|wmc|wmf|woc|wow|woa|wnf|wmp|wmv|tre|trd|sld|sli|slk|slc|slb|skp|sky|sll|slp|smk|sml|smm|smf|smc|slt|slv|sit|sis|shk|shm|shp|shg|shb|sgt|sh3|shr|sht|sig|sik|sim|sif|sib|shw|shx|smn|smp|spw|spx|sqd|spt|sps|spp|spr|sqi|sql|srf|srp|srt|src|sqz|sqm|sqn|spm|spl|sno|snp|sol|sng|snd|sms|smt|som|son|spg|spi|spf|spd|sou|spc|sgp|sgi|scd|scf|sch|scc|sca|sc3|sc4|sci|scm|sct|scx|scy|scr|scp|scn|sco|sbt|sbr|saf|sal|sam|sab|s7i|s3i|s3m|san|sar|sbk|sbl|sbp|sbi|sbd|sat|sav|mli|sda|sff|sfi|sfl|sfd|sfb|set|sf2|sfn|sfo|sfx|sg1|sgf|sft|sfs|sfp|sfr|ses|seq|sdl|sdm|sdn|sdk|sdi|sdc|sdf|sdr|sds|sen|sep|sec|sea|sdw|sdx|ssc|ssd|tes|tet|tex|tem|tel|tec|tef|tfa|tfc|tg1|tga|tgz|tfs|tfm|tff|tfh|tdw|tdt|tch|tcl|tct|tbz|tbx|tbr|tbs|tcw|td0|tdk|tdr|tds|tdh|tdf|td2|tdb|the|thm|tp3|tpb|tpc|tos|tok|tmy|toc|tpf|tph|tpz|tr2|trc|tpw|tpu|tpl|tpp|tms|tmp|til|tis|tjl|tif|tic|ths|tib|tlb|tlc|tmf|tmo|tm2|tlp|tld|tlh|tbl|tbk|sty|sub|suf|stx|stw|sts|stu|sui|sum|svg|svq|svs|svd|sup|sun|suo|str|stp|sst|ssv|sta|ssr|ssp|ssf|ssg|stb|stc|stl|stm|sto|stk|sti|std|stf|svw|svx|t2l|t44|t64|t2b|syw|syn|sys|tag|tah|tb1|tb2|tbf|taz|tar|tal|tap|sym|syg|swt|sxc|sxd|swp|swg|svy|swf|sxg|sxi|sy3|syd|sy1|sxw|sxm|sxp|s1k|mmp|dcp|dcs|dct|dcm|dcl|dch|dci|dcu|dcw|ddp|dds|deb|ddi|ddb|dcx|dda|dcf|dcd|dbg|dbk|dbl|dbf|dbd|dba|dbc|dbm|dbo|dc6|dca|dcc|dbx|dbw|dbs|dbt|def|dem|diz|dkb|dld|dis|dir|dig|dip|dlf|dlg|dmf|dmg|dmo|dls|dlp|dll|dlm|dif|dic|dfi|dfl|dfm|dff|dfd|des|dev|dfs|dfv|dht|dia|dib|dhp|dgs|dfx|dgn|db3|db2|cpc|cpd|cpf|con|com|cof|col|cpi|cpl|cpz|cr2|cra|cpt|cps|cpp|cpr|coe|cod|cmf|cmk|cmm|cmd|cmb|cls|clw|cmp|cms|cnf|cnv|cob|cnd|cnc|cmu|cmv|crd|crf|cvp|cvs|cvt|cv4|cut|cuf|cur|cvw|cwk|daa|daf|dat|d64|d4p|cxx|d4d|ctx|ctt|cru|csd|csg|crt|crs|crk|crp|csm|csp|ctk|ctl|ctf|ctc|css|csv|dmp|dms|epi|eps|eqn|epd|env|end|eng|erd|erl|mlb|etw|etx|etf|esh|erm|err|enc|emu|eft|efv|efx|efs|efq|efe|efk|ega|eka|emd|emf|eml|elt|eli|elb|elc|eui|evt|f96|fac|fam|f95|f90|f4v|f77|faq|far|fdb|fds|fdw|fcm|fbm|fas|fax|f4p|f4b|exe|exf|exm|exc|ex3|evy|ewd|exp|ext|f2r|f3r|f4a|f03|f01|exx|ezf|efa|eeb|dsk|dsm|dsn|dsf|dsd|ds4|dsc|dsp|dsr|dt1|dta|dtb|dsy|dsw|dss|dst|ds1|drw|dog|doh|dor|dof|doc|dng|dnt|dos|dot|drp|drs|drv|dpx|dpr|dox|doz|dtf|dtl|ecc|ece|ed5|ebq|ebj|eas|eba|eda|ede|eds|edt|edv|edq|edl|edf|edk|ear|e2d|dvi|dvp|dw2|dvc|dtp|dtm|dto|dwb|dwc|dxp|dyn|dxn|dxf|dwd|dwg|clr|clp|arj|ark|arr|ari|arh|ard|arf|art|arx|ash|asi|ask|asf|ase|asc|asd|arc|ar7|aos|apc|apd|aol|ans|anm|ann|ape|apf|apr|apw|apx|app|apm|api|apl|asm|aso|awa|awd|awk|avt|avs|avi|avr|awm|aws|azz|b1n|b30|azw|azd|axf|axm|avb|ava|asx|asz|at2|asw|ast|asp|ass|at3|atm|aup|aux|auz|aud|aty|atn|att|ani|ana|ad2|ad3|ada|act|aco|acl|acm|adb|adc|adl|adm|adn|adi|adg|add|adf|acf|ace|ab6|ab8|abc|aas|aaf|a80|aac|abf|abk|aca|acb|acc|abs|abr|abm|abp|adr|ads|ale|alg|all|alc|aix|aio|ais|alo|alt|aml|amr|ams|amg|amf|alz|amd|ain|aif|aep|af2|af3|adz|adx|adt|adv|afi|afl|agr|ahk|afw|aft|afm|afp|bac|bad|cbz|ccc|ccf|cbt|cbs|cbm|cbr|cch|ccl|cdf|cdk|cdl|cdb|cda|cco|cct|cbl|cbc|c01|c86|cab|c00|bzm|byu|bz2|cac|cad|cas|cat|cba|cap|can|cal|cam|cdm|cdr|chl|chm|chn|chk|chi|ch4|chd|chp|chr|ckb|cla|cld|cix|cif|cht|chz|ch3|cgm|ceg|cel|cfg|cef|ceb|cdt|cdx|cfl|cfm|cga|cgi|cft|cfp|cfn|cfo|bwr|bwb|bf2|bfc|bfm|bez|bdt|bdm|bdr|bfx|bga|bin|bio|bit|bik|bif|bgi|bib|bdf|bdc|bat|bbl|bbm|bas|bar|bak|bal|bbp|bbs|bct|bcw|bdb|bcp|bco|bcf|bch|bkp|bkw|bsc|bsp|bss|bsa|bs2|brk|brl|bst|bsy|bup|but|buy|bun|bug|btm|btn|brd|bpt|bmk|bmp|bmt|bm3|blk|bld|blg|bna|bnk|bpc|bpp|bot|boo|bnr|bok|feb|eth|izt|j2c|jam|iwp|iwd|ivu|iwa|jar|jas|jet|jff|jif|jcn|jbx|jav|jbd|ivp|ivl|irt|isd|ish|irs|ipt|ipn|ips|iso|ist|ive|ivi|itt|itn|itf|itl|jms|job|kep|kex|key|kdc|kdb|kbm|kcl|kfx|kit|kno|kpl|kpp|kmz|kmy|kmc|kml|kbd|kar|jpg|jre|jrn|jpc|jp2|jor|jou|jsp|jtf|k3d|kap|k2a|jzz|jvx|jwl|ipl|iph|ica|icb|icc|ibm|ibg|iam|iax|ice|icm|ide|idf|ids|idc|idb|icn|ico|iak|hyp|htm|htx|hwd|hst|hsi|hsc|hsh|hwp|hxm|hyc|hyd|hy2|hy1|hxs|hxx|idw|idx|inf|ini|ink|ind|inc|in3|inb|ins|int|ipc|ipf|ipg|ion|ioc|inx|iob|imq|imp|igc|ihp|ihs|ifs|ifp|ifd|iff|iiw|ilb|img|imi|ima|im8|ilg|ilk|kps|kr1|mcd|mcf|mci|mcc|mbx|max|mbk|mcl|mcp|mda|mdb|mdf|md5|md3|mcw|md2|mat|mas|lzs|lzw|lzx|lzh|lzd|lxt|lyr|m3u|m4a|map|mar|man|mak|m7p|mai|mdl|mdm|mib|mid|mii|mht|mgx|mfm|mgf|mio|mis|mks|mkv|ml3|mki|mkg|mix|mke|mff|mfa|mdz|meb|mec|mdx|mdt|mdr|mds|med|mem|meu|mex|met|mes|meq|mer|lxf|lwp|lcs|lcw|ld1|lcn|lcl|lch|lck|lda|ldb|lfd|lfl|lft|lex|lev|ldf|les|lcf|lbx|kyb|la0|lab|ktu|ks3|kr2|krz|lan|lay|lbr|lbt|lbo|lbm|lbg|lbl|lgo|lha|lrx|lsa|lsb|lrs|lrp|lrc|lrf|lsp|lss|lwd|lwo|lua|ltm|lst|lta|lpc|lom|lim|lin|lis|lif|lic|lhw|lib|lit|lix|lod|log|lnk|lng|ll3|lmp|hs2|igf|giw|gkh|gif|fo2|fog|gid|gks|fo1|fnm|glm|fnt|glb|fnx|fol|gib|gfo|gft|fot|gfb|gex|for|gfx|ghs|ghw|fon|gho|fop|fnk|glo|fmo|gp5|fmt|goh|goe|fmv|fmk|gph|gr2|fmb|gpx|gpk|fmf|goc|gmt|gm6|fnd|gly|gls|fni|fn3|gmd|gmp|gms|gml|gmk|gmf|geo|gen|fpw|fpu|fr3|frf|frg|ftx|fpt|fw2|fxd|fxp|fpd|fpm|fw3|ftp|ftm|fsa|frp|frs|frx|frt|fro|frm|fst|fsx|frl|fsm|fsl|fxs|fyf|gcx|gd3|gcf|gcd|gc3|gca|gdf|gdl|hrz|gem|fox|ged|gds|gc1|fp3|g3p|fp5|fp7|g16|fpc|fp4|gam|gbl|gbr|gbc|gba|gb1|gra|gp4|hex|hfi|hep|hdz|hdx|hgl|fh4|hhp|ffi|fft|fh3|hhh|fif|hdw|fio|hcg|hbk|hbe|hbc|hdd|fin|hdr|fig|hdl|fil|hdf|hin|fff|hpj|hpk|hpi|hpg|hpf|hpm|fes|hrf|hrm|fei|hqx|hpp|hp8|hof|hlp|hlx|hlb|hkm|his|hlz|hmi|hnc|hmp|ffe|hmm|fit|hds|grp|gsa|flm|grn|flc|flv|flb|gwi|gs3|gvo|gry|gta|gs1|fli|gup|hap|fld|gxl|grf|fm1|grb|fky|gsm|hal|fix|ham|fm3|flx|grd|gzr|gsd|fla|gsw|gre|flt|flp|ar|mk|fc|rn|md|ff|mm|fm|ng|sd|nc|sh|xi|ai|rc|wn|fd|wp|rs|xx|ms|rh|lz|ws|xy|fp|xp|ri|mf|sc|ad|mu|f4|sf|xa|sb|ap|rf|el|xe|yy|yz|nb|xm|rm|yc|rv|ly|mb|me|fi|ac|mz|z3|bf|gv|io|ip|ui|dd|ps|ce|gz|tz|ty|cf|ha|ub|u8|ul|cd|v8|uw|pb|ca|dh|iw|uu|iv|cc|sw|it|pc|pt|dc|hc|tp|pj|td|id|pp|hi|co|pk|tf|cs|pm|pl|hp|cm|tc|il|tb|ts|im|tv|ch|pg|fe|tr|hh|db|ph|cl|vb|ix|ob|sm|ld|qt|aw|le|g8|b8|so|ks|sn|bb|ba|gb|fx|wd|np|ra|ec|lj|lm|nl|au|sl|nt|dx|fw|lg|r8|ge|sp|vi|br|px|js|gl|ss|pw|dl|di|gp|vc|jk|st|jw|py|bi|dp|qb|vs|vw|ds|q0|vp|bm|vm|kb|vo|bk|rb)$/i',
				'remove_string' => true,
				'whitelist' => array(),
			),
			'sha1' => array(
				'regex' => '/\b[a-f0-9]{40}\b/i',
				'regex_validate' => '/^[a-f0-9]{40}$/i',
				'remove_string' => true,
				'whitelist' => array(),
				'group' => 'hash',
			),
			'sha256' => array(
				'regex' => '/\b[a-f0-9]{64}\b/i',
				'regex_validate' => '/^[a-f0-9]{64}$/i',
				'remove_string' => true,
				'whitelist' => array(),
				'group' => 'hash',
			),
			'md5' => array(
				'regex' => '/\b[a-f0-9]{32}\b/i',
				'regex_validate' => '/^[a-f0-9]{32}$/i',
				'remove_string' => true,
				'whitelist' => array(),
				'group' => 'hash',
			),
			'md5_34' => array(
				'regex' => '/\b[a-f0-9]{34}\b/i',
				'regex_validate' => '/^[a-f0-9]{34}$/i',
				'remove_string' => true,
				'whitelist' => array(),
				'group' => 'hash',
			),
			'mac' => array(
				'regex' => '/\b([a-f0-9]{12}|[a-f0-9]{2}\-[a-f0-9]{2}\-[a-f0-9]{2}\-[a-f0-9]{2}\-[a-f0-9]{2}\-[a-f0-9]{2}|[a-f0-9]{2}\:[a-f0-9]{2}\:[a-f0-9]{2}\:[a-f0-9]{2}\:[a-f0-9]{2}\:[a-f0-9]{2})\b/i',
				'regex_validate' => '/^([a-f0-9]{12}|[a-f0-9]{2}\-[a-f0-9]{2}\-[a-f0-9]{2}\-[a-f0-9]{2}\-[a-f0-9]{2}\-[a-f0-9]{2}|[a-f0-9]{2}\:[a-f0-9]{2}\:[a-f0-9]{2}\:[a-f0-9]{2}\:[a-f0-9]{2}\:[a-f0-9]{2})$/i',
				'remove_string' => true,
				'whitelist' => array(),
			),
			'ssdeep' => array(
				'regex' => '/\b\d+\:[a-zA-Z0-9\/\+]+\:[a-zA-Z0-9\/\+]+\b/i',
				'regex_validate' => '/^\d+\:[a-zA-Z0-9\/\+]+\:[a-zA-Z0-9\/\+]+$/i',
				'remove_string' => true,
				'whitelist' => array(
					'/\d+\:\d+\:\d+/',
				),
			),
			// "\x5c" is a backslash
			'netbios' => array(
				'regex' => '/\b\w+\x5c[\w\-\_]+\b/i',
				'regex_validate' => '/^\w+\x5c[\w\-\_]+$/i',
				'remove_string' => true,
				'whitelist' => array(),
			),
			'asset_tag' => array(
				'regex' => '/\bOR(S|F)\-[0-9]{8}\b/i',
				'regex_validate' => '/^OR(S|F)\-[0-9]{8}$/i',
				'remove_string' => true,
				'whitelist' => array(),
			),
			'phone_number_us' => array(
				'regex' => '/(\(?[0-9]{3}(\)|-)?[\s\-]?)[0-9]{3}\-[0-9]{4}\b/',
				'regex_validate' => '/^(\(?[0-9]{3}(\)|-)?[\s\-]?)[0-9]{3}\-[0-9]{4}$/',
				'remove_string' => true,
			),
			'integer' => array(
				'regex' => '/^\d+$/i',
				'regex_validate' => '/^\d+$/i',
				'remove_string' => true,
				'whitelist' => array(),
			),
			'float' => array(
				'regex' => '/^\d+\.\d+$/i',
				'regex_validate' => '/^\d+\.\d+$/i',
				'remove_string' => true,
				'whitelist' => array(),
			),
			'string' => array(
				'regex' => '/^[a-z0-9]+$/i',
				'regex_validate' => '/^[a-z0-9]+$/i',
				'remove_string' => true,
				'whitelist' => array(),
			),
		),
		
		// if we should search and remove dot variants
		'normalize_dot_variants' => true,
		
		// the dot variants we need to replace
		'dot_variants' => array(
			'{.}', '{ .}', '{. }', '{ . }', 
			'[.]', '[ .]', '[. ]', '[ . ]', 
			'{dot}', '{ dot}', '{dot }', '{ dot }', 
			'[dot]', '[ dot]', '[dot ]', '[ dot ]', 
			),
	);
	
	// from a previous vendor class i wrote. see the getFilenames function below
	public $extensions = array('a11', 'a80', 'aac', 'aaf', 'aas', 'ab6', 'ab8', 'abc', 'abf', 'abk', 'abm', 'abp', 'abr', 'abs', 'ac', 'aca', 'acb', 'acc', 'accda', 'accdb', 'accde', 'accdt', 'accdu', 'ace', 'acf', 'acis', 'acl', 'acm', 'acmb', 'aco', 'act', 'ad', 'ad2', 'ad3', 'ada', 'adb', 'adc', 'add', 'adf', 'adg', 'adi', 'adicht', 'adl', 'adm', 'adn', 'adr', 'ads', 'adt', 'adv', 'adx', 'adz', 'aep', 'af2', 'af3', 'afi', 'afl', 'afm', 'afp', 'aft', 'afw', 'agr', 'ahk', 'ai', 'aif', 'aifc', 'aiff', 'ain', 'aio', 'ais', 'aix', 'alc', 'ale', 'alg', 'all', 'alo', 'alt', 'alz', 'amd', 'amf', 'amg', 'aml', 'amr', 'ams', 'ana', 'ani', 'anm', 'ann', 'ans', 'aol', 'aos', 'ap', 'apc', 'apd', 'ape', 'apf', 'api', 'apl', 'apm', 'app', 'apr', 'apw', 'apx', 'ar', 'ar7', 'arc', 'ard', 'arf', 'arh', 'ari', 'arj', 'ark', 'arr', 'art', 'arx', 'asc', 'asd', 'ase', 'asf', 'ash', 'asi', 'ask', 'asm', 'asmx', 'aso', 'asp', 'aspx', 'ass', 'ast', 'asw', 'asx', 'asz', 'at2', 'at3', 'atm', 'atn', 'att', 'aty', 'au', 'aud', 'aup', 'aux', 'auz', 'ava', 'avb', 'avi', 'avr', 'avs', 'avt', 'aw', 'awa', 'awd', 'awk', 'awm', 'aws', 'axf', 'axm', 'azd', 'azw', 'azz', 'b1n', 'b30', 'b8', 'ba', 'bac', 'bad', 'bak', 'bal', 'balance', 'bar', 'bas', 'bat', 'bb', 'bbl', 'bbm', 'bbp', 'bbs', 'bcf', 'bch', 'bco', 'bcp', 'bct', 'bcw', 'bdb', 'bdc', 'bdef', 'bdf', 'bdm', 'bdr', 'bdt', 'bez', 'bf', 'bf2', 'bfc', 'bfm', 'bfx', 'bga', 'bgi', 'bi', 'bib', 'bif', 'bik', 'bin', 'bio', 'bit', 'bk', 'bkp', 'bkw', 'bld', 'blend', 'blg', 'blk', 'bm', 'bm3', 'bmk', 'bmp', 'bmt', 'bna', 'bnk', 'bnr', 'bok', 'boo', 'bot', 'bpc', 'bpoly', 'bpp', 'bpt', 'br', 'brd', 'brk', 'brl', 'bs2', 'bsa', 'bsc', 'bsp', 'bss', 'bst', 'bsy', 'btm', 'btn', 'bufr', 'bug', 'bun', 'bup', 'but', 'buy', 'bwb', 'bwr', 'byu', 'bz2', 'bzm', 'c00', 'c01', 'c86', 'ca', 'cab', 'cac', 'cad', 'cal', 'cals', 'cam', 'can', 'cap', 'cas', 'cat', 'cats', 'cba', 'cbc', 'cbl', 'cbm', 'cbr', 'cbs', 'cbt', 'cbz', 'cc', 'ccc', 'ccf', 'cch', 'ccitt', 'ccl', 'cco', 'cct', 'cd', 'cda', 'cdb', 'cdf', 'cdk', 'cdl', 'cdm', 'cdr', 'cdt', 'cdx', 'ce', 'ceb', 'cef', 'ceg', 'cel', 'cf', 'cfg', 'cfl', 'cfm', 'cfn', 'cfo', 'cfp', 'cft', 'cga', 'cgi', 'cgm', 'ch', 'ch3', 'ch4', 'chd', 'chi', 'chk', 'chl', 'chm', 'chml', 'chn', 'chp', 'chr', 'cht', 'chz', 'cif', 'cix', 'ckb', 'cl', 'cla', 'class', 'cld', 'clp', 'clr', 'cls', 'clw', 'cm', 'cmb', 'cmd', 'cmf', 'cmk', 'cmm', 'cmp', 'cmrl', 'cms', 'cmu', 'cmv', 'cnc', 'cnd', 'cnf', 'cnv', 'co', 'cob', 'cod', 'coe', 'cof', 'col', 'com', 'con', 'cpc', 'cpd', 'cpf', 'cpi', 'cpl', 'cpmz', 'cpp', 'cpr', 'cps', 'cpt', 'cpz', 'cr2', 'cra', 'crd', 'crf', 'crk', 'crp', 'crs', 'crt', 'cru', 'cs', 'csd', 'csg', 'csm', 'csp', 'css', 'csv', 'ctc', 'ctf', 'ctk', 'ctl', 'ctt', 'ctx', 'cuf', 'cur', 'cut', 'cv4', 'cvp', 'cvs', 'cvt', 'cvw', 'cweb', 'cwk', 'cxx', 'd4d', 'd4p', 'd64', 'daa', 'daf', 'dat', 'dats', 'db', 'db2', 'db3', 'dba', 'dbc', 'dbd', 'dbf', 'dbg', 'dbk', 'dbl', 'dbm', 'dbo', 'dbs', 'dbt', 'dbw', 'dbx', 'dc', 'dc6', 'dca', 'dcc', 'dcd', 'dcf', 'dch', 'dci', 'dcl', 'dcm', 'dcp', 'dcs', 'dct', 'dcu', 'dcw', 'dcx', 'dd', 'dda', 'ddb', 'ddi', 'ddp', 'dds', 'deb', 'def', 'dem', 'des', 'desklink', 'dev', 'dfd', 'dff', 'dfi', 'dfl', 'dfm', 'dfs', 'dfv', 'dfx', 'dgn', 'dgs', 'dh', 'dhp', 'dht', 'di', 'dia', 'dib', 'dic', 'dif', 'dig', 'dip', 'dir', 'dis', 'diz', 'djvu', 'dkb', 'dl', 'dld', 'dlf', 'dlg', 'dll', 'dlm', 'dlp', 'dls', 'dmf', 'dmg', 'dmo', 'dmp', 'dms', 'dng', 'dnt', 'doc', 'docm', 'docx', 'dof', 'dog', 'doh', 'dor', 'dos', 'dot', 'dotx', 'dox', 'doz', 'dp', 'dpr', 'dpx', 'drp', 'drs', 'drv', 'drw', 'ds', 'ds1', 'ds4', 'dsc', 'dsd', 'dsf', 'dsk', 'dsm', 'dsn', 'dsp', 'dsr', 'dss', 'dst', 'dsw', 'dsy', 'dt1', 'dta', 'dtb', 'dtf', 'dtl', 'dtm', 'dto', 'dtp', 'dvc', 'dvdproj', 'dvi', 'dvp', 'dw2', 'dwb', 'dwc', 'dwd', 'dwg', 'dx', 'dxf', 'dxn', 'dxp', 'dylib', 'dyn', 'e2d', 'ear', 'eas', 'eba', 'ebj', 'ebq', 'ec', 'ecc', 'ece', 'ecms', 'ecmt', 'ed5', 'eda', 'ede', 'edf', 'edk', 'edl', 'edq', 'eds', 'edt', 'edv', 'eeb', 'efa', 'efe', 'efk', 'efq', 'efs', 'eft', 'efv', 'efx', 'ega', 'eka', 'el', 'elb', 'elc', 'eli', 'elt', 'emaker', 'emd', 'emf', 'eml', 'emu', 'enc', 'end', 'enff', 'eng', 'env', 'epd', 'epi', 'eps', 'eqn', 'erd', 'erl', 'erm', 'err', 'esh', 'etf', 'eth', 'etw', 'etx', 'eui', 'evt', 'evy', 'ewd', 'ex3', 'exc', 'exe', 'exf', 'exm', 'exp', 'ext', 'exx', 'ezf', 'f01', 'f03', 'f2r', 'f3r', 'f4', 'f4a', 'f4b', 'f4p', 'f4v', 'f77', 'f90', 'f95', 'f96', 'fac', 'face', 'fam', 'faq', 'far', 'fas', 'fax', 'fbm', 'fc', 'fcm', 'fd', 'fdb', 'fds', 'fdw', 'fe', 'feb', 'fei', 'fes', 'ff', 'ffe', 'fff', 'ffi', 'ffivw', 'fft', 'fh3', 'fh4', 'fi', 'fif', 'fig', 'fil', 'film', 'fin', 'fio', 'fit', 'fits', 'fix', 'fky', 'fla', 'flac', 'flame', 'flb', 'flc', 'fld', 'fli', 'flm', 'flp', 'flt', 'flv', 'flx', 'fm', 'fm1', 'fm3', 'fmb', 'fmf', 'fmk', 'fmo', 'fmt', 'fmv', 'fn3', 'fnd', 'fni', 'fnk', 'fnm', 'fnt', 'fnx', 'fo1', 'fo2', 'fog', 'fol', 'fon', 'font', 'fop', 'for', 'fot', 'fox', 'fp', 'fp3', 'fp4', 'fp5', 'fp7', 'fpc', 'fpd', 'fpm', 'fpt', 'fpu', 'fpw', 'fr3', 'frf', 'frg', 'frl', 'frm', 'fro', 'frp', 'frs', 'frt', 'frx', 'fsa', 'fsim', 'fsl', 'fsm', 'fsproj', 'fst', 'fsx', 'ftm', 'ftp', 'ftx', 'ftxt', 'fw', 'fw2', 'fw3', 'fweb', 'fx', 'fxd', 'fxp', 'fxs', 'fyf', 'g16', 'g3p', 'g8', 'gam', 'gb', 'gb1', 'gba', 'gbc', 'gbl', 'gbr', 'gc1', 'gc3', 'gca', 'gcd', 'gcf', 'gcx', 'gd3', 'gdf', 'gdl', 'gds', 'ge', 'ged', 'gem', 'gen', 'geo', 'gex', 'gfb', 'gfo', 'gft', 'gfx', 'gho', 'ghs', 'ghw', 'gib', 'gid', 'gif', 'giw', 'gkh', 'gks', 'gl', 'glb', 'glm', 'glo', 'gls', 'gly', 'gm6', 'gmd', 'gmf', 'gmk', 'gml', 'gmod', 'gmp', 'gms', 'gmt', 'goc', 'goe', 'goh', 'gp', 'gp4', 'gp5', 'gph', 'gpk', 'gpx', 'gr2', 'gra', 'graffle', 'grasp', 'grb', 'grd', 'grdnt', 'gre', 'grf', 'grib', 'grn', 'grp', 'gry', 'gs1', 'gs3', 'gsa', 'gsd', 'gsm', 'gsw', 'gta', 'gup', 'gv', 'gvo', 'gwi', 'gxl', 'gz', 'gzr', 'ha', 'hal', 'ham', 'hap', 'hbc', 'hbe', 'hbk', 'hc', 'hcg', 'hcgs', 'hdd', 'hdf', 'hdl', 'hdmp', 'hdr', 'hds', 'hdw', 'hdx', 'hdz', 'hep', 'hex', 'hfi', 'hgl', 'hh', 'hhh', 'hhp', 'hi', 'hin', 'his', 'hkm', 'hlb', 'hlp', 'hlx', 'hlz', 'hmi', 'hmm', 'hmp', 'hnc', 'hof', 'hp', 'hp8', 'hpf', 'hpg', 'hpgl', 'hpi', 'hpj', 'hpk', 'hpm', 'hpp', 'hppcl', 'hqx', 'hrf', 'hrm', 'hrz', 'hs2', 'hsc', 'hsh', 'hsi', 'hst', 'htm', 'html', 'htx', 'hwd', 'hwp', 'hxm', 'hxs', 'hxx', 'hy1', 'hy2', 'hyc', 'hyd', 'hyp', 'iak', 'iam', 'iax', 'ibg', 'ibm', 'ica', 'icb', 'icc', 'ice', 'icm', 'icn', 'ico', 'id', 'idb', 'idc', 'ide', 'idf', 'ids', 'idw', 'idx', 'ifd', 'iff', 'ifp', 'ifs', 'igc', 'iges', 'igf', 'ihp', 'ihs', 'iiw', 'il', 'ilb', 'ilg', 'ilk', 'im', 'im8', 'ima', 'img', 'imi', 'imp', 'imq', 'in3', 'inb', 'inc', 'ind', 'indd', 'inf', 'info', 'ingr', 'ini', 'ink', 'ins', 'int', 'inx', 'io', 'iob', 'ioc', 'ion', 'ip', 'ipc', 'ipf', 'ipg', 'iph', 'ipl', 'ipn', 'ips', 'ipt', 'irs', 'irt', 'irtr', 'isd', 'ish', 'isma', 'iso', 'ist', 'it', 'itf', 'itl', 'itn', 'itt', 'iv', 'ive', 'ivi', 'ivl', 'ivp', 'ivu', 'iw', 'iwa', 'iwd', 'iwp', 'ix', 'izt', 'j2c', 'jam', 'jar', 'jas', 'jasc', 'jav', 'java', 'jbd', 'jbig', 'jbx', 'jcn', 'jet', 'jff', 'jfif', 'jif', 'jk', 'jms', 'job', 'jor', 'jou', 'jp2', 'jpc', 'jpeg', 'jpg', 'jre', 'jrn', 'js', 'jsp', 'jtf', 'jvx', 'jw', 'jwl', 'jzz', 'k2a', 'k3d', 'kap', 'kar', 'kb', 'kbasic', 'kbd', 'kbm', 'kcl', 'kdb', 'kdbx', 'kdc', 'kep', 'kex', 'key', 'kfx', 'kit', 'kmc', 'kml', 'kmy', 'kmz', 'kno', 'kpl', 'kpp', 'kps', 'kr1', 'kr2', 'krz', 'ks', 'ks3', 'ktu', 'kyb', 'la0', 'lab', 'lan', 'lay', 'lbg', 'lbl', 'lbm', 'lbo', 'lbr', 'lbt', 'lbx', 'lcf', 'lch', 'lck', 'lcl', 'lcn', 'lcs', 'lcw', 'ld', 'ld1', 'lda', 'ldb', 'ldf', 'le', 'les', 'lev', 'lex', 'lfd', 'lfl', 'lft', 'lg', 'lgo', 'lha', 'lhw', 'lib', 'lic', 'lif', 'lim', 'lin', 'lis', 'lit', 'lix', 'lj', 'll3', 'lm', 'lmp', 'lng', 'lnk', 'lod', 'log', 'lom', 'lpc', 'lrc', 'lrf', 'lrp', 'lrs', 'lrx', 'lsa', 'lsb', 'lsp', 'lss', 'lst', 'lta', 'ltm', 'lua', 'lwd', 'lwo', 'lwp', 'lxf', 'lxt', 'ly', 'lyr', 'lz', 'lzd', 'lzh', 'lzs', 'lzw', 'lzx', 'm3u', 'm3u8', 'm4a', 'm7p', 'mai', 'mak', 'man', 'map', 'mar', 'mas', 'mat', 'max', 'mb', 'mbk', 'mbx', 'mcc', 'mcd', 'mcf', 'mci', 'mcl', 'mcp', 'mcw', 'md', 'md2', 'md3', 'md5', 'mda', 'mdb', 'mdf', 'mdl', 'mdm', 'mdmp', 'mdr', 'mds', 'mdt', 'mdx', 'mdz', 'me', 'meb', 'mec', 'med', 'mem', 'meq', 'mer', 'mes', 'met', 'meu', 'mex', 'mf', 'mfa', 'mff', 'mfm', 'mgf', 'mgx', 'mht', 'mib', 'mid', 'midi', 'miff', 'mii', 'mime', 'mio', 'mis', 'mix', 'mk', 'mke', 'mkg', 'mki', 'mks', 'mkv', 'ml3', 'mlb', 'mli', 'mlraw', 'mlv', 'mm', 'mmf', 'mmm', 'mmo', 'mmp', 'mnd', 'mng', 'mnt', 'mnu', 'mnx', 'mny', 'mob', 'mobi', 'mod', 'mol', 'mol2', 'mon', 'mop', 'mov', 'mp2', 'mp3', 'mp4', 'mpa', 'mpc', 'mpeg', 'mpg', 'mpkg', 'mpl', 'mpm', 'mpp', 'mpq', 'mpr', 'mpt', 'mpv', 'mpx', 'mpz', 'mrb', 'mrc', 'mrk', 'mrs', 'ms', 'msc', 'msdl', 'mse', 'msg', 'msi', 'msn', 'mso', 'msp', 'msqm', 'mss', 'mst', 'msw', 'msx', 'mth', 'mtl', 'mtm', 'mts', 'mtv', 'mtw', 'mu', 'mus', 'muz', 'mvf', 'mvi', 'mvo', 'mvw', 'mwf', 'mws', 'mxf', 'mxl', 'mxm', 'mxp', 'mxt', 'myp', 'mz', 'mze', 'mzg', 'mzx', 'nam', 'nap', 'naplps', 'nb', 'nbf', 'nc', 'ncc', 'ncd', 'ncp', 'ndb', 'ndl', 'nds', 'ndx', 'neo', 'nes', 'net', 'neu', 'new', 'nff', 'nfo', 'ng', 'ngo', 'ngs', 'nif', 'nil', 'nitf', 'nk2', 'nl', 'nlm', 'nls', 'nlx', 'not', 'np', 'npi', 'npj', 'nrg', 'nrw', 'nsa', 'nsf', 'nss', 'nst', 'nsv', 'nsx', 'nt', 'ntf', 'nth', 'ntr', 'nts', 'ntx', 'nuf', 'numbers', 'nws', 'nxt', 'o01', 'oaz', 'ob', 'obd', 'obj', 'objf', 'obr', 'obs', 'obt', 'obv', 'obz', 'ocd', 'ocf', 'ocr', 'oct', 'ocx', 'odb', 'odf', 'odg', 'odif', 'odl', 'odm', 'odp', 'ods', 'odt', 'ofd', 'off', 'ofm', 'ofn', 'ogg', 'ohb', 'ohs', 'oif', 'okt', 'olb', 'old', 'oli', 'olsr', 'oma', 'oof', 'oog', 'oom', 'opj', 'opn', 'opt', 'opw', 'opx', 'ora', 'org', 'otb', 'otg', 'oth', 'otl', 'otp', 'ots', 'ott', 'otx', 'out', 'ov1', 'ov2', 'ovd', 'ovl', 'ovr', 'oxt', 'p10', 'p12', 'p16', 'p22', 'p3t', 'p80', 'p86', 'pa1', 'pac', 'pack', 'package', 'pad', 'paf', 'pages', 'pak', 'pal', 'pan', 'par', 'par2', 'part', 'pas', 'pat', 'pb', 'pb1', 'pba', 'pbd', 'pbi', 'pbk', 'pbl', 'pbm', 'pbo', 'pbt', 'pc', 'pc3', 'pc8', 'pca', 'pcb', 'pcc', 'pcd', 'pcf', 'pch', 'pcj', 'pck', 'pcl', 'pcm', 'pcs', 'pct', 'pcw', 'pcx', 'pda', 'pdb', 'pdd', 'pde', 'pdf', 'pdg', 'pdi', 'pdl', 'pdm', 'pdn', 'pds', 'pdv', 'pdw', 'peb', 'ped', 'pem', 'peq', 'per', 'pes', 'pet', 'pex', 'pfa', 'pfb', 'pfc', 'pfk', 'pfm', 'pfs', 'pft', 'pg', 'pgd', 'pgi', 'pgl', 'pgm', 'pgp', 'pgs', 'ph', 'phd', 'phm', 'phn', 'pho', 'php', 'php3', 'php4', 'phr', 'pi2', 'pic', 'pict', 'pie', 'pif', 'pit', 'piv', 'pix', 'pj', 'pjt', 'pjx', 'pk', 'pk2', 'pk3', 'pk4', 'pka', 'pkg', 'pkt', 'pl', 'pl1', 'pl2', 'pl3', 'pla', 'plb', 'plc', 'pld', 'ple', 'plist', 'pll', 'plm', 'pln', 'plp', 'pls', 'plsc', 'plsk', 'plt', 'ply', 'ply2', 'pm', 'pm3', 'pm4', 'pm5', 'pmc', 'pmi', 'pmm', 'pmp', 'pn3', 'pnf', 'png', 'pnm', 'pnt', 'poh', 'pol', 'policy', 'poly', 'pop', 'pot', 'pov', 'pow', 'pp', 'ppa', 'ppb', 'ppd', 'ppj', 'ppk', 'ppl', 'ppm', 'ppo', 'ppp', 'pps', 'ppt', 'pptx', 'pr1', 'pr2', 'pr3', 'prc', 'prd', 'pre', 'prf', 'prg', 'pri', 'prj', 'prk', 'prm', 'prn', 'pro', 'proj', 'proto', 'prr', 'prs', 'prt', 'prx', 'ps', 'psa', 'psb', 'psd', 'pse', 'pseg', 'psf', 'psm', 'psn', 'psp', 'psq', 'pst', 'psv', 'psw', 'pt', 'pt3', 'pt4', 'pt5', 'ptb', 'ptf', 'ptg', 'ptl', 'ptm', 'ptn', 'ptr', 'ptu', 'pub', 'pud', 'put', 'pvd', 'pvl', 'pvm', 'pvt', 'pw', 'pwf', 'pwk', 'pwl', 'pwp', 'px', 'pxp', 'pxr', 'pxz', 'py', 'pyc', 'pyi', 'pyo', 'pzd', 'pzi', 'pzo', 'pzp', 'pzs', 'pzt', 'pzx', 'q0', 'q3s', 'qag', 'qap', 'qb', 'qbb', 'qbe', 'qbo', 'qbw', 'qch', 'qcp', 'qdf', 'qdk', 'qdr', 'qdv', 'qef', 'qfx', 'qhc', 'qhcp', 'qhp', 'qif', 'qiz', 'qlb', 'qlc', 'qlp', 'qm4', 'qpr', 'qpx', 'qrs', 'qrt', 'qry', 'qt', 'qtvr', 'qvd', 'qvw', 'qwk', 'qxd', 'qxl', 'r2d', 'r3d', 'r8', 'r8p', 'ra', 'rad', 'ral', 'ram', 'rar', 'ras', 'rat', 'raw', 'ray', 'rb', 'rbf', 'rc', 'rcg', 'rdf', 'rdi', 'rdp', 'rdx', 'rec', 'red', 'ref', 'reg', 'rel', 'rem', 'rep', 'req', 'res', 'rev', 'rex', 'rez', 'rf', 'rft', 'rgb', 'rgx', 'rh', 'ri', 'rib', 'ric', 'rif', 'rip', 'rix', 'rl4', 'rl8', 'rla', 'rlb', 'rlc', 'rld', 'rle', 'rlz', 'rm', 'rmb', 'rmi', 'rmk', 'rmvb', 'rn', 'rnd', 'rno', 'rol', 'rom', 'roo', 'rpb', 'rpd', 'rpf', 'rpl', 'rpm', 'rpt', 'rrd', 'rs', 'rsc', 'rsl', 'rsp', 'rsy', 'rtf', 'rtfd', 'rtl', 'rtp', 'rts', 'rul', 'run', 'runz', 'rus', 'rv', 'rvf', 'rvw', 'rws', 'rwx', 'rwz', 'rxd', 'rzk', 'rzx', 's19', 's1k', 's3i', 's3m', 's7i', 'sab', 'saf', 'safe', 'saif', 'sal', 'sam', 'san', 'sar', 'sat', 'sats', 'sav', 'saver', 'sb', 'sbd', 'sbi', 'sbk', 'sbl', 'sbp', 'sbr', 'sbt', 'sc', 'sc3', 'sc4', 'sca', 'scc', 'scd', 'scf', 'sch', 'sci', 'scm', 'scn', 'sco', 'scp', 'scr', 'sct', 'sctor', 'scx', 'scy', 'sd', 'sd7', 'sda', 'sdc', 'sdf', 'sdi', 'sdk', 'sdl', 'sdm', 'sdml', 'sdn', 'sdr', 'sds', 'sdts', 'sdw', 'sdx', 'sea', 'sec', 'sen', 'sep', 'seq', 'ses', 'set', 'sf', 'sf2', 'sfb', 'sfd', 'sff', 'sfi', 'sfl', 'sfn', 'sfo', 'sfp', 'sfr', 'sfs', 'sft', 'sfx', 'sg1', 'sgf', 'sgi', 'sgp', 'sgt', 'sh', 'sh3', 'shar', 'shb', 'shg', 'shk', 'shm', 'shp', 'shr', 'sht', 'shw', 'shx', 'sib', 'sif', 'sig', 'sik', 'sim', 'sis', 'sit', 'skp', 'sky', 'sl', 'slb', 'slc', 'sld', 'sldasm', 'slddrw', 'sldprt', 'sli', 'slk', 'sll', 'slp', 'slt', 'slv', 'sm', 'smc', 'smf', 'smk', 'sml', 'smm', 'smn', 'smp', 'smpl', 'sms', 'smt', 'sn', 'snd', 'sndb', 'sng', 'sno', 'snp', 'so', 'soar', 'sol', 'som', 'son', 'sou', 'sp', 'spc', 'spd', 'spf', 'spg', 'spi', 'spiff', 'spin', 'spl', 'spm', 'spp', 'spr', 'sps', 'spt', 'spw', 'spx', 'sqd', 'sqi', 'sql', 'sqm', 'sqn', 'sqz', 'src', 'srf', 'srp', 'srt', 'ss', 'ssc', 'ssd', 'ssf', 'ssg', 'ssp', 'ssr', 'sst', 'ssv', 'st', 'sta', 'stb', 'stc', 'std', 'stf', 'sti', 'stk', 'stl', 'stm', 'sto', 'stp', 'str', 'sts', 'stu', 'stw', 'stx', 'sty', 'sub', 'suf', 'sui', 'sum', 'sun', 'suo', 'sup', 'svd', 'svg', 'svq', 'svs', 'svw', 'svx', 'svy', 'sw', 'swf', 'swg', 'swp', 'swt', 'sxc', 'sxd', 'sxg', 'sxi', 'sxm', 'sxp', 'sxw', 'sy1', 'sy3', 'syd', 'syg', 'sym', 'syn', 'sys', 'syw', 't2b', 't2l', 't44', 't64', 'tag', 'tah', 'tal', 'tap', 'tar', 'taz', 'tb', 'tb1', 'tb2', 'tbf', 'tbk', 'tbl', 'tbr', 'tbs', 'tbx', 'tbz', 'tc', 'tch', 'tcl', 'tct', 'tcw', 'td', 'td0', 'td2', 'tdb', 'tddd', 'tdf', 'tdh', 'tdk', 'tdr', 'tds', 'tdt', 'tdw', 'tec', 'tef', 'tel', 'tem', 'term', 'tes', 'tet', 'tex', 'text', 'tf', 'tfa', 'tfc', 'tff', 'tfh', 'tfm', 'tfs', 'tg1', 'tga', 'tgz', 'the', 'thm', 'ths', 'tib', 'tic', 'tif', 'tiff', 'til', 'tis', 'tjl', 'tlb', 'tlc', 'tld', 'tlh', 'tlp', 'tm2', 'tmf', 'tmo', 'tmp', 'tms', 'tmy', 'tmy2', 'toc', 'tok', 'topc', 'torrent', 'tos', 'tp', 'tp3', 'tpb', 'tpc', 'tpf', 'tph', 'tpl', 'tpoly', 'tpp', 'tpu', 'tpw', 'tpz', 'tr', 'tr2', 'trc', 'trd', 'tre', 'tria', 'trib', 'trif', 'trk', 'trm', 'trn', 'trs', 'tru', 'trw', 'try', 'ts', 'tse', 'tsk', 'tsm', 'tst', 'tsv', 'ttf', 'tud', 'tut', 'tv', 'tvf', 'txf', 'txi', 'txt', 'txw', 'ty', 'tym', 'tz', 'tzb', 'tzl', 'tzx', 'u3d', 'u8', 'ub', 'uc2', 'ucn', 'ucs', 'udf', 'udiwww', 'udo', 'udw', 'ue2', 'ufi', 'uha', 'uhs', 'ui', 'uif', 'uih', 'ul', 'uld', 'ulf', 'ult', 'umb', 'unf', 'uni', 'unif', 'unx', 'uoml', 'uop', 'uos', 'uot', 'upd', 'upo', 'url', 'urt', 'use', 'usp', 'usr', 'utz', 'uu', 'uue', 'uw', 'uwf', 'v10', 'v2d', 'v8', 'vai', 'val', 'vala', 'van', 'vap', 'var', 'vb', 'vbproj', 'vbr', 'vbs', 'vbx', 'vbz', 'vc', 'vc6', 'vca', 'vcd', 'vcr', 'vcw', 'vcx', 'vda', 'vdo', 'vdr', 'veg', 'vew', 'vfa', 'vfm', 'vfn', 'vga', 'vgd', 'vgr', 'vi', 'vic', 'vicar', 'vid', 'vif', 'viff', 'vik', 'vim', 'vinf', 'vir', 'vis', 'vlm', 'vm', 'vmc', 'vmdk', 'vmf', 'vmg', 'vms', 'vo', 'vob', 'voc', 'vof', 'vop', 'vox', 'vp', 'vpf', 'vpg', 'vph', 'vpi', 'vqa', 'vrm', 'vrml', 'vrp', 'vrs', 'vs', 'vsd', 'vsm', 'vsp', 'vss', 'vst', 'vue', 'vw', 'vwr', 'vwx', 'vxd', 'vzt', 'w01', 'w30', 'w31', 'w3a', 'w3b', 'w3d', 'w3g', 'w3h', 'w3m', 'w3n', 'w3o', 'w3p', 'w3q', 'w3t', 'w3u', 'w3v', 'w3x', 'w3z', 'w40', 'w44', 'w95', 'wad', 'wag', 'wai', 'war', 'was', 'wav', 'wax', 'wb1', 'wb2', 'wbf', 'wbk', 'wbt', 'wcd', 'wch', 'wcm', 'wcp', 'wd', 'wdb', 'wdm', 'web', 'wfb', 'wfd', 'wfm', 'wfn', 'wfp', 'wft', 'wfx', 'wg1', 'wg2', 'wid', 'wim', 'win', 'wix', 'wiz', 'wk1', 'wk3', 'wk4', 'wkb', 'wke', 'wki', 'wkq', 'wks', 'wkz', 'wlk', 'wma', 'wmc', 'wmdb', 'wmf', 'wmp', 'wmv', 'wn', 'wnf', 'woa', 'woc', 'wow', 'wp', 'wp5', 'wpd', 'wpf', 'wpg', 'wpj', 'wpk', 'wpl', 'wpm', 'wps', 'wq1', 'wr1', 'wrd', 'wri', 'wrk', 'wrl', 'wrp', 'wrs', 'ws', 'ws2', 'wsd', 'wsp', 'wsq', 'wsrc', 'wst', 'wtf', 'wtr', 'wtt', 'wwb', 'wwk', 'wxh', 'wxp', 'x3d', 'xa', 'xar', 'xbe', 'xbm', 'xcf', 'xck', 'xcl', 'xdf', 'xdm', 'xe', 'xex', 'xfn', 'xfr', 'xft', 'xi', 'xif', 'xla', 'xlb', 'xlc', 'xld', 'xlk', 'xll', 'xlm', 'xls', 'xlsb', 'xlsx', 'xlt', 'xlv', 'xlw', 'xm', 'xmf', 'xmi', 'xml', 'xmod', 'xmpz', 'xmv', 'xnf', 'xof', 'xp', 'xpm', 'xps', 'xqt', 'xrf', 'xsf', 'xspf', 'xtb', 'xtp', 'xvf', 'xwd', 'xwk', 'xwp', 'xx', 'xxe', 'xy', 'xy3', 'xyw', 'xyz', 'yal', 'yc', 'ymp', 'yrd', 'yst', 'yuv', 'yy', 'yz', 'z2s', 'z3', 'z3d', 'z64', 'z80', 'zad', 'zdg', 'zed', 'zer', 'zip');
	
	// mime types of files that we can parse
	// map a mimetype to a function that can extract the string
	public $parsableMimeTypes = array(
		'text/plain' => 'fileContent_text',
		'text/csv' => 'fileContent_text',
		'application/pdf' => 'fileContent_pdf',
	);
	
	// Used to hold the external php class for pdf tools
	public $Pdftools = false;
	
	// common/stop words for extracting keywords for an object
	public $commonWords = array("\d+", "\d+(am|pm|st|th|rd)", "able", "about", "above", "abst", "accordance", "according\w*", "account\w{0,4}", "accurate", "across", "activit\w*", "action\w*", "activ\w*", "actually", "add\w{1,3}", "\w{1}ffect\w*", "agenda", "allow\w*", "affecting", "after\w*", "again\w*", "alert\w*", "almost", "alone", "along", "already", "also", "although", "always", "among", "amongst", "announce", "another", "anybody", "anyhow", "anymore", "anyone", "anything", "anyway", "anyways", "anywhere", "apparently", "application\w*", "approximately", "aren", "arent", "arise", "around", "aside", "ask\w*", "assign\w*", "assist\w*", "associat\w*", "attempt\w*", "attend\w*", "attribut\w*", "attention", "auth", "available", "aware\w*", "away", "awfully", "back", "background", "balanc\w*", "base(d|s)*", "basic\w*", "basis", "began", "bec(a|o)me\w*", "because", "been", "before", "beforehand", "begin", "beginning", "beginnings", "begins", "behind", "being", "believe", "belong\w*", "below", "beside\w*", "between", "beyond", "biol", "board\w*", "boiler\w*", "both", "brief", "briefly", "buil(d|t)\w*", "bulk", "call\w*", "came", "cancel\w*", "cannot", "can't", "case\w*", "caught", "cause", "causes", "center", "certain", "certainly", "change\w*", "charge", "come", "comes", "connect\w*", "contain", "containing", "contains", "continu\w*", "could", "couldnt", "count(s)*", "cover\w*", "date(s)*", "detail\w*", "didn't", "different", "discuss\w*", "document\w*", "does", "doesn't", "doing", "done", "don't", "down", "downwards", "during", "each\w*", "edit\w*", "effort\w*", "eight\w*", "either", "else", "elsewhere", "ending", "enough", "especially", "et-al", "even", "ever", "every", "everybody", "everyone", "everything", "everywhere", "except", "exist\w*", "extra", "fifth", "final\w*", "find\w*", "first", "five", "fix\w*", "followed", "following", "follows", "former", "formerly", "forth", "found", "four", "from", "full", "further", "furthermore", "gave", "general", "gets", "getting", "give", "given", "gives", "giving", "goes", "going", "gone", "gotten", "group\w*", "happens", "hardly", "hasn't", "have", "haven't", "having", "h(e|o)ld", "hence", "here", "hereafter", "hereby", "herein", "heres", "hereupon", "hers", "herself", "himself", "hither", "home", "howbeit", "however", "hundred", "includ\w*", "i'll", "immediate", "immediately", "importance", "important", "indeed", "index", "info\w*", "instead", "into", "invention", "inward", "isn't", "it'll", "item\w*", "itself", "i've", "just", "keep 	keeps", "kept", "know", "known", "knows", "largely", "last", "late\w*", "latter\w*", "least", "less", "lest", "lets", "like", "liked", "likely", "line", "list\w*", "little", "'ll", "look", "looking", "looks", "made", "mainly", "make", "makes", "many", "maybe", "mean", "means", "meantime", "meanwhile", "merely", "might", "million", "miss", "more", "moreover", "most", "mostly", "much", "must", "myself", "name", "namely", "near\w*", "necessar\w*", "need\w*", "neither", "never", "nevertheless", "next", "nine", "ninety", "nobody", "none", "nonetheless", "noone", "normally", "noted", "nothing", "nowhere", "obtain", "obtained", "obviously", "often", "okay", "omitted", "once", "ones", "only", "onto", "other", "others", "otherwise", "ought", "ours", "ourselves", "outside", "over", "overall", "owing", "page", "pages", "part", "particular", "particularly", "past", "perhaps", "place\w*", "please", "plus", "poorly", "possible", "possibly", "potentially", "predominantly", "present", "previously", "primarily", "probably", "promptly", "proud", "provides", "quickly", "quite", "rather", "readily", "really", "recent", "recently", "refs", "regarding", "regardless", "regards", "related", "relatively", "research", "respectively", "resulted", "resulting", "results", "right", "said", "same", "saying", "says", "section", "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sent", "seven", "several", "shall", "shed", "she'll", "shes", "should", "shouldn't", "show", "showed", "shown", "showns", "shows", "significant", "significantly", "similar", "similarly", "since", "slightly", "some", "somebody", "somehow", "someone", "somethan", "something", "sometime", "sometimes", "somewhat", "somewhere", "soon", "sorry", "specifically", "specified", "specify", "specifying", "still", "stop", "strongly", "substantially", "successfully", "such", "sufficiently", "suggest", "sure", "take", "taken", "taking", "tell", "tends", "than", "thank", "thanks", "thanx", "that", "that'll", "thats", "that've", "their", "theirs", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "thered", "therefore", "therein", "there'll", "thereof", "therere", "theres", "thereto", "thereupon", "there've", "these", "they", "theyd", "they'll", "theyre", "they've", "think", "this", "those", "thou", "though", "thoughh", "thousand", "throug", "through", "throughout", "thru", "thus", "together", "took", "toward", "towards", "tried", "tries", "truly", "trying", "twice", "under", "unfortunately", "unless", "unlike", "unlikely", "until", "unto", "upon", "used", "useful", "usefully", "usefulness", "uses", "using", "usually", "value", "various", "very", "vols", "want", "wants", "wasnt", "welcome", "we'll", "went", "were", "werent", "we've", "what", "whatever", "what'll", "whats", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "wheres", "whereupon", "wherever", "whether", "which", "while", "whim", "whither", "whod", "whoever", "whole", "who'll", "whom", "whomever", "whos", "whose", "widely", "willing", "wish", "with", "within", "without", "wont", "words", "world", "would", "wouldnt", "youd", "you'll", "your", "youre", "yours", "yourself", "yourselves", "you've", "zero", "work", "week", "vars", "tool", "time", "test\w*");
	
	// list if items to be trimmed off the end of keywords
	public $trimItems = array('.', '\'', '"', '-', '_');
	
	public function setup(Model $Model, $config = array()) 
	{
	/*
	 * Set everything up
	 */
		// merge the default settings with the model specific settings
		$this->settings[$Model->alias] = array_merge($this->_defaults, $config);
	}
	
	public function extractItemsFromFile(Model $Model, $file_path = false, $mimetype = false)
	{
	/*
	 * Extracts a string from a file and looks for vectors in that string
	 */
		
		if(!trim($file_path)) return false;
		if(!is_file($file_path)) return false;
		if(!is_readable($file_path)) return false;
		
		if(!$mimetype)
		{
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, $file_path);
			finfo_close($finfo);
		}
		if(!in_array($mimetype, array_keys($this->parsableMimeTypes))) return false;
		
		$function = $this->parsableMimeTypes[$mimetype];
		
		$string = $this->$function($Model, $file_path);
		
		$items = $this->extractItems($Model, $string);
		
		return $items;
	}
	
	public function extractItems(Model $Model, $string = '')
	{
		$out = array();
		
		if(!trim($string)) return $out;
		
		// clean the string up, and remove dot variants if needed
		$string = $this->cleanString($Model, $string);
		
		// add a space at the end to help with the regex
		$string .= ' ';
		
		// extract the different type of vectors from the string
		foreach($this->settings[$Model->alias]['extractors'] as $type => $settings)
		{
			$out[$type] = array();
			
			// make sure we have a regex to use
			if(!isset($settings['regex'])) continue;
			
			// find all of the types
			if(preg_match_all($settings['regex'], $string, $matches))
			{
				if(isset($matches[0]))
				{
					$items = array_flip($matches[0]);
					$items = array_flip($items);
					
					foreach($items as $i => $item)
					{	
						$items[$i] = trim($item);
						
						// clean up the item
						// check the cleaNUP
						if(isset($settings['regex_replace']))
						{
							foreach($settings['regex_replace'] as $regex => $replace)
							{
								$items[$i] = preg_replace($regex, $replace, $items[$i]);
							}
						}
						

						// validate the item
						if(isset($settings['regex_validate']))
						{
							if(!preg_match($settings['regex_validate'], $items[$i]))
							{
								unset($items[$i]);
								continue;
							}
						}
						
						// check the whitelist
						$whitelistMatch = false;
						if(isset($settings['whitelist']))
						{
							foreach($settings['whitelist'] as $regex)
							{
								if(preg_match($regex, $items[$i]))
								{
									unset($items[$i]);
									$whitelistMatch = true;
									break;
								}
							}
						}
						
						// check if we should remove the item from the string
						if(!$whitelistMatch and isset($settings['remove_string']) and $settings['remove_string'])
						{
							$string = str_replace($items[$i], '', $string);
						}
					}
					
					// remove duplicates
					$items = array_flip($items);
					$items = array_flip($items);
					
					sort($items);
					$out[$type] = $items;
				}
			}
		}
		return $out;
	}
	
	public function obfuscateString(Model $Model, $string = '')
	{
		if(!$string) return $string;
		
		$extracted_items = $this->extractItems($Model, $string);
		foreach($extracted_items as $k => $items)
		{
			if(!count($items)) continue;
			
			$obfuscaters = array();
			if(isset($this->settings[$Model->alias]['extractors'][$k]) and 
			isset($this->settings[$Model->alias]['extractors'][$k]['obfuscate']) )
			{
				$obfuscaters = $this->settings[$Model->alias]['extractors'][$k]['obfuscate'];
			}
			foreach($items as $item)
			{
				$fixeditem = $item;
				foreach($obfuscaters as $search => $replace)
				{
					$fixeditem = str_replace($search, $replace, $fixeditem);
				}
				$string = str_replace($item, $fixeditem, $string);
			}
		}
		return $string;
	}
	
	public function extractKeywords(Model $Model, $string = false, $file = false)
	{
	/*
	 * Extracts items from the data (text) and returns them
	 */
		$out = array();
		
		if(!isset($string) and !isset($file)) return false;
		
		if($file)
		{
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, $file);
			finfo_close($finfo);
			
			if(in_array($mimetype, array_keys($this->parsableMimeTypes)))
			{
				$function = $this->parsableMimeTypes[$mimetype];
				$string .= $this->$function($Model, $file);
			}
		}
		
		$string = strtolower($string);
		$string = $this->cleanString($Model, $string);
		
		// sanitize the string
		$string = $this->paranoidSpace($Model, $string, array(' ', '@', '.', '-', '_', '\''));
		// remove all common/stop words
		$string = preg_replace('#\b('.implode('|',$this->commonWords).')\b#','', $string);
		
		// clean up each word
		$string = preg_split('/\s+/', $string);
		foreach($string as $i => $item)
		{
			$item = trim($item);
			$item = trim($item, implode('', $this->trimItems));
			
			// keywords must be at least 3 characters long
			if(strlen($item) <= 3) continue;
			$out[$item] = $item;
			
		}
		
		unset($string);
		sort($out);
		
		return $out;
	}
	
	public function whiteList(Model $Model, $items = null, $regex_ref = null)
	{
		if($items and $regex_ref)
		{
			$regexes = array();
			if(isset($this->settings[$Model->alias]['whitelist'][$regex_ref]))
			{
				$regexes = $this->settings[$Model->alias]['whitelist'][$regex_ref];
			}
			
			if(empty($regexes) or !is_array($regexes))
			{
				return $items;
			}
			
			foreach($items as $i => $string)
			{
				foreach($regexes as $regex)
				{
					if(preg_match($regex, $string))
					{
						unset($items[$i]);
						break;
					}
				}
			}
		}
		
		$items = array_flip($items);
		$items = array_flip($items);
		
		return $items;
	}
	
//
	public function cleanString(Model $Model, $string = '')
	{
	/*
	 * Cleans the text up
	 */
		if($string)
		{
			// replaces: [.], [. ], [ .], [ . ] - with a period '.'
			$string = preg_replace('/[\s+]?\[[\s+]?(dot|\.|\:)[\s+]?\][\s+]?/i', '.', $string);
			// replaces: {.}, {. }, { .}, { . } - with a period '.'
			$string = preg_replace('/[\s+]?\{[\s+]?(dot|\.|\:)[\s+]?\}[\s+]?/i', '.', $string);
			// changes hxxp to http
			$string = preg_replace('/(hxxp|httx)(\s+)?/i', 'http', $string);
			// removes html break tags (<br />)
			$string = preg_replace('/\<br\/?\>/i', ' ', $string);
			$string = preg_replace('/\/\/\]/i', '//', $string);
			$string = $this->utf8toISO8859_1($Model, $string);
			$string = preg_replace('/([\200-\277])/e', '', $string);
			
			if($this->settings[$Model->alias]['normalize_dot_variants'])
			{
				$string = str_replace($this->settings[$Model->alias]['dot_variants'], '.', $string);
			}
			
			// normalize it to be within utf8
		}
		return $string;
	}
	
	public function fileContent_text(Model $Model, $file_path = false)
	{
		return file_get_contents($file_path);
	}
	
//
	public function fileContent_pdf(Model $Model, $file_path = false)
	{
	/*
	 * Extract the string for a plain text file
	 */
		// load the PhpPdf Plugin's Behavior
		if(!CakePlugin::loaded('PhpPdf'))
			CakePlugin::load('PhpPdf');
		
		if(!$Model->Behaviors->loaded('PhpPdf.PhpPdf'))
		{
			$Model->Behaviors->load('PhpPdf.PhpPdf');
		}
		
		if($Model->Behaviors->loaded('PhpPdf'))
			return $Model->PhpPdf_getText($file_path);
		
		return false;
	}
	
//
	public function validatedHostnames(Model $Model, $domains = array())
	{
	/*
	 * Validates a domain by looking at its tld and comparing it to a known list
	 */
	
		// make sure domains is an array
		if(is_string($domains)) $domain = array($domains);
		
		if(!$tldlist = $this->getTLDlist($Model))
		{
			return $domains;
		}
		
		foreach($domains as $i => $domain)
		{
			$domain_parts = explode('.', $domain);
			$last = array_pop($domain_parts);
			$last2 = array_pop($domain_parts);
			$last_ext = implode('.', array($last2, $last));
			
			if(in_array($last, $tldlist)) continue;
			if(in_array($last_ext, $tldlist)) continue;
			
			// it's not in the list
			unset($domains[$i]);
		}
		return $domains;
	}
	
//
	public function validatedUrls(Model $Model, $urls = array())
	{
	/*
	 * Validates a url by looking at the tld of it's domain name and comparing it to a known list
	 */
	
		// make sure domains is an array
		if(is_string($urls)) $urls = array($urls);
		
		if(!$tldlist = $this->getTLDlist($Model))
		{
			return $urls;
		}
		
		foreach($urls as $i => $url)
		{
			$url_parts = explode('/', $url);
			if(!isset($url_parts[2]))
			{
				unset($urls[$i]);
				continue;
			}
			$domain_parts = explode('.', $url_parts[2]);
			
			$last = array_pop($domain_parts);
			$last2 = array_pop($domain_parts);
			$last_ext = implode('.', array($last2, $last));
			
			if(in_array($last, $tldlist)) continue;
			if(in_array($last_ext, $tldlist)) continue;
			
			// it's not in the list
			unset($urls[$i]);
		}
		return $urls;
	}
	
	public function EX_isHash(Model $Model, $string = false)
	{
		if($string) return false;
		
		$string = trim($string);
		$type = $this->EX_discoverType($Model, $string);
		$hash_types = $this->EX_listTypes($Model, 'hash');
		
		if(in_array($type, array_keys($hash_types))) return true;
		return false;
	}
	
//
	public function getTLDlist(Model $Model)
	{
	/*
	 * Gets a list of tlds from Mozilla that can drop cookies
	 *	//http://mxr.mozilla.org/mozilla-central/source/netwerk/dns/effective_tld_names.dat?raw=1
	 */
		$out = Cache::read('getTLDlist', 'external');
		if ($out !== false)
		{
			return $out;
		}
		
		$data = @file_get_contents('http://mxr.mozilla.org/mozilla-central/source/netwerk/dns/effective_tld_names.dat?raw=1');
		if(!$data) return false;
		
		$data = explode("\n", $data);
		$out = array();
		foreach($data as $line)
		{
			$line = strtolower(trim($line));
			if(preg_match('/^[a-zA-Z]/', $line)) $out[] = $line;
		}
		$out = array_flip($out);
		$out = array_flip($out);
		
		Cache::write('getTLDlist', $out, 'external');
		
		return $out;
	}
	
	public function EX_listTypes(Model $Model, $group = false)
	{
		$out = array();
		foreach($this->settings[$Model->alias]['extractors'] as $type => $settings)
		{
			if($group)
			{
				if(isset($settings['group']) and $settings['group'] == $group)
					$out[$type] = Inflector::humanize($type);
			}
			else
			{
				$out[$type] = Inflector::humanize($type);
			}
		}
		return $out;
	}
	
//
	public function EX_discoverType(Model $Model, $string = false)
	{
		if(!$string) return false;
		
		foreach($this->settings[$Model->alias]['extractors'] as $type => $settings)
		{
			if(preg_match($settings['regex_validate'], $string, $matches))
			{
				// check the whitelist
				$whitelistMatch = false;
				if(isset($settings['whitelist']))
				{
					foreach($settings['whitelist'] as $regex)
					{
						if(preg_match($regex, $string))
						{
							$whitelistMatch = true;
							break;
						}
					}
				}
				
				// check if we should remove the item from the string
				if($whitelistMatch)
				{
					continue;
				}
				return $type;
			}
		}
		
		return 'unknown';
	}
	
	public function EX_fixMacAddress(Model $Model, $mac = false)
	{
		$mac = trim($mac);
		if(!$mac)
			return $mac;
		$mac = strtoupper($mac);
		$mac = preg_replace('/(\s+|\-|\:)/', '', $mac);
		return $mac;
	}
	
	public function EX_fixAssetTag(Model $Model, $assetTag = false)
	{
		$assetTag = trim($assetTag);
		if(!$assetTag)
			return $assetTag;
		$mac = strtoupper($assetTag);
		return $assetTag;
	}
	
	// stolen from Sanitize::paranoid
	// instead of removing the item, it replaces it with a space
	public static function paranoidSpace(Model $Model, $string, $allowed = array()) 
	{
		$allow = null;
		if (!empty($allowed)) 
		{
			foreach ($allowed as $value) 
			{
				$allow .= "\\$value";
			}
		}
		
		if (is_array($string)) 
		{
			$cleaned = array();
			foreach ($string as $key => $clean) 
			{
				$cleaned[$key] = preg_replace("/[^{$allow}a-zA-Z0-9]/", ' ', $clean);
			}
		} 
		else 
		{
			$cleaned = preg_replace("/[^{$allow}a-zA-Z0-9]/", ' ', $string);
		}
		return $cleaned;
	}
	
	public function utf8toISO8859_1(Model $Model, $string)
	{
	/*
	 * replaces characters wirh their Western types
	 * removes the rest of the utf8 characters
	 */
		$accented = array(
			'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ă', 'Ą',
			'Ç', 'Ć', 'Č', 'Œ',
			'Ď', 'Đ',
			'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ă', 'ą',
			'ç', 'ć', 'č', 'œ',
			'ď', 'đ',
			'È', 'É', 'Ê', 'Ë', 'Ę', 'Ě',
			'Ğ',
			'Ì', 'Í', 'Î', 'Ï', 'İ',
			'Ĺ', 'Ľ', 'Ł',
			'è', 'é', 'ê', 'ë', 'ę', 'ě',
			'ğ',
			'ì', 'í', 'î', 'ï', 'ı',
			'ĺ', 'ľ', 'ł',
			'Ñ', 'Ń', 'Ň',
			'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ő',
			'Ŕ', 'Ř',
			'Ś', 'Ş', 'Š',
			'ñ', 'ń', 'ň',
			'ò', 'ó', 'ô', 'ö', 'ø', 'ő',
			'ŕ', 'ř',
			'ś', 'ş', 'š',
			'Ţ', 'Ť',
			'Ù', 'Ú', 'Û', 'Ų', 'Ü', 'Ů', 'Ű',
			'Ý', 'ß',
			'Ź', 'Ż', 'Ž',
			'ţ', 'ť',
			'ù', 'ú', 'û', 'ų', 'ü', 'ů', 'ű',
			'ý', 'ÿ',
			'ź', 'ż', 'ž',
			'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р',
			'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'р',
			'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
			'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
			);

		$replace = array(
			'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'A', 'A',
			'C', 'C', 'C', 'CE',
			'D', 'D',
			'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'a', 'a',
			'c', 'c', 'c', 'ce',
			'd', 'd',
			'E', 'E', 'E', 'E', 'E', 'E',
			'G',
			'I', 'I', 'I', 'I', 'I',
			'L', 'L', 'L',
			'e', 'e', 'e', 'e', 'e', 'e',
			'g',
			'i', 'i', 'i', 'i', 'i',
			'l', 'l', 'l',
			'N', 'N', 'N',
			'O', 'O', 'O', 'O', 'O', 'O', 'O',
			'R', 'R',
			'S', 'S', 'S',
			'n', 'n', 'n',
			'o', 'o', 'o', 'o', 'o', 'o',
			'r', 'r',
			's', 's', 's',
			'T', 'T',
			'U', 'U', 'U', 'U', 'U', 'U', 'U',
			'Y', 'Y',
			'Z', 'Z', 'Z',
			't', 't',
			'u', 'u', 'u', 'u', 'u', 'u', 'u',
			'y', 'y',
			'z', 'z', 'z',
			'A', 'B', 'B', 'r', 'A', 'E', 'E', 'X', '3', 'N', 'N', 'K', 'N', 'M', 'H', 'O', 'N', 'P',
			'a', 'b', 'b', 'r', 'a', 'e', 'e', 'x', '3', 'n', 'n', 'k', 'n', 'm', 'h', 'o', 'p',
			'C', 'T', 'Y', 'O', 'X', 'U', 'u', 'W', 'W', 'b', 'b', 'b', 'E', 'O', 'R',
			'c', 't', 'y', 'o', 'x', 'u', 'u', 'w', 'w', 'b', 'b', 'b', 'e', 'o', 'r'
			);
		$string = str_replace($accented, $replace, $string);
		$string = utf8_decode($string);
		
		return $string;
	}
	
	// from a previous vendor class i wrote
	// here for reference later if needed
	public function getFilenames($whiteListKey = 'filenames')
	{
		$filenames = false;
		if($this->text)
		{
			$filenames = $this->getFilePaths();
			if(!$filenames)
			{
				$filenames = array();
			}
		
			if(preg_match_all('/[\/\w\-\_\.\(\)]+\.[a-z]{2,4}/i', $this->text, $matches))
			{
				if(isset($matches[0]))
				{
					$filenames = am($filenames, $matches[0]);
					$filenames = array_flip(array_flip($filenames));
					
					foreach($filenames as $i => $filename)
					{
						$filename = trim($filename);
						$filenames[$i] = array_pop(explode('\\', $filename));
						$filenames[$i] = array_pop(explode('/', $filenames[$i]));
					}
					$filenames = array_flip(array_flip($filenames));
					
					// see if it's extension is in the known extensions above
					foreach($filenames as $i => $filename)
					{
						$ext = array_pop(explode('.', $filename));
						$ext = strtoupper($ext);
						if(!in_array($ext, $this->extensions))
						{
							unset($filenames[$i]);
						}
					}
					
					// make sure it's not in the hostames 
					$hostnames = $this->getHostnames();
					
					foreach($filenames as $i => $filename)
					{
						$filenames[$i] = strtolower($filename);
						
						// make sure it's not in the hostames 
						if(is_array($hostnames) and !empty($hostnames) and in_array($filenames[$i], $hostnames))
						{
							unset($filenames[$i]);
						}
					}
					
					$filenames = $this->whiteList($filenames, $whiteListKey);					
					sort($filenames);
				}
			}
		}
		return $filenames;
	}
}