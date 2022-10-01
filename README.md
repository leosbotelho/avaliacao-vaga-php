1.
a) com `static::`  
[extra](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/1a.md)

b) com `parent::`  
[extra](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/1b.md)

c) as de 'programação multiparadigmática', procedural e OO, com namespaces

d) não é possível acessar propriedades não estáticas da própria classe ou de ancestrais em métodos estáticos;
a não ser através de objetos que ou são recebidos como parâmetros, ou acessados pelo ambiente (eg 'variáveis globais'), ou instanciados nesses próprios métodos estáticos

<br>

2. Código em [resposta](https://github.com/leosbotelho/avaliacao-vaga-php/tree/main/resposta); básicamente:
- [routes/web](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/resposta/routes/web.php)
- [routes/api](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/resposta/routes/api.php)
- [Middleware/AdminToken](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/resposta/app/Http/Middleware/AdminToken.php#L19)
- [Controllers/Admin/Api/User](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/resposta/app/Http/Controllers/Admin/Api/User.php#L10)
- [Console/Kernel](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/resposta/app/Console/Kernel.php#L19), schedule
- [PasswordResetLinkController](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/resposta/app/Http/Controllers/Auth/PasswordResetLinkController.php#L29)
- [Jobs/PasswordResetMail](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/resposta/app/Jobs/PasswordResetMail.php#L33)
- várias outras modificações pequenas

Online em https://floating-island-97183.herokuapp.com

Usei [Breeze](https://laravel.com/docs/9.x/starter-kits#laravel-breeze) como starter kit e pro painel; [Sanctum](https://laravel.com/docs/9.x/sanctum) pra tokens de APIs.

Cheque com o [Postman](https://www.postman.com), importando [essa](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/resposta.postman_collection.json) coleção.

[extra](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/2.md)

Obs: a redefinição de senha só funciona local (usei o gmail).

<br>

3. Depende do banco de dados; vou supor o MariaDB.

a)
```
create or replace table T (
  id int auto_increment primary key,
  s char(1)
);

insert into T(id, s) values(2147483647, 'a');

-- erro
-- insert into T(s) values('b');

alter table T modify column id int default (0 - cast(uuid_short() % 2147483647 as int));

-- agora ok
insert into T(s) values('b');
```

A ideia aqui é usar ids negativos únicos, mas não sequenciais.

Eu suponho que evitar [IDORs](https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/05-Authorization_Testing/04-Testing_for_Insecure_Direct_Object_References) esteja relacionado a como esse espaço de mais de 2 bilhões de valores do domínio de id tenha sido exaurido.

Bom, `uuids` também não são especialmente imprevisíveis.

Poderiamos - fácilmente - prover UDFs de [nanoid](https://github.com/leosbotelho/nanoid-c), com `uuids` imprevisíveis.

Por enquanto, tome `md5_u64(uuid_short())`.

Como só podemos usar `builtin functions` na expressão de um valor padrão, expandimos minha `user-defined function` `md5_u64` - e ficamos com:
```
alter table T modify column id int default (0 - cast(conv(substring(md5(uuid_short()), 1, 16), 16, 10) % 2147483647 as int));
```

E agora nossos - novos - ids além de únicos são imprevisíveis;  
&nbsp; nosso sistema é mais resistente a `IDORs`.

<br>

Vantagens:
- uma única alteração no banco de dados
- zero downtime do banco de dados

Desvantagens:
- possíveis incompatibilidades, com integrações
- possíveis modificações na aplicação:
  - não exibir ids negativos aos usuários
  - substituir expressões regulares pertinentes, `[0-9]+` por `-?[0-9]+`

<br>

Outra possibilidade:
```
set foreign_key_checks=0;
alter table C modify column Aid bigint auto_increment;
alter table B modify column Aid bigint auto_increment;
alter table A modify column id bigint auto_increment;
set foreign_key_checks=1;
```

Tomando:
```
create or replace table A (
  id int auto_increment primary key
);

create or replace table B (
  Aid int primary key,
  foreign key (Aid) references A (id)
);

create or replace table C (
  Aid int primary key,
  foreign key (Aid) references B (Aid)
);
```

E para abordar as causas subjacentes, substitua `bigint auto_increment` por:
```
bigint unsigned default (conv(substring(md5(uuid_short()), 1, 16), 16, 10) | 2147483648)
```

<br>

Vantagens:
- provavalmente não requer modificações na aplicação

Desvantagens:
- várias alterações no banco de dados, que podem ser enfadonhas
- downtime do banco de dados

<br>

As soluções acima devem ser adaptadas ao sistema de migrações implementado.

<br>

b) Padronize os ids como `bigint unsigned` com `default md5_u64(x)`, `x = uuid_short()`.

Serve qualquer `hash` de 64 bits expressa como um número positivo;  
&nbsp; e adequados geradores de ids únicos (eg [nanoid](https://github.com/ai/nanoid)).

Você pode usar `int unsigned` com 32 bits;  
&nbsp; mas aí podem haver casos nos quais você vai ter que calcular,  
&nbsp; &nbsp; eg por essa [base](https://zelark.github.io/nano-id-cc/), se os ids são únicos o suficiente.

c) A únicidade, global, das Chaves Primárias já está garantida dessa forma.

4. Em geral, otimizar:
- relevância e formato da requisição e resposta
- banco de dados (eg `configuração`, `chaves`, `índices`)
- ORM (eg queries `n + 1`)
- queries
- cache: http; do banco de dados; etc

5. Através de [queues](https://laravel.com/docs/9.x/queues).


Demonstrado por [PasswordResetLinkController](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/resposta/app/Http/Controllers/Auth/PasswordResetLinkController.php#L29)
e [Jobs/PasswordResetMail](https://github.com/leosbotelho/avaliacao-vaga-php/blob/main/resposta/app/Jobs/PasswordResetMail.php#L33);  
&nbsp; pela - funcionalidade de - redefinição de senha.
