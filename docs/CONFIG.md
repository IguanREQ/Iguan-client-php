# Config

Best way to create [Emitter](../src/Iguan/Event/Emitter/EventEmitter.php)
and [Subscriber](../src/Iguan/Event/Subscriber/EventSubscriber.php) is to use a [Builder](../src/Iguan/Event/Builder/Builder.php).

[Builder](../src/Iguan/Event/Builder/Builder.php) can create [Emitter](../src/Iguan/Event/Emitter/EventEmitter.php) 
and [Subscriber](../src/Iguan/Event/Subscriber/EventSubscriber.php) with all dependencies from [Config](../src/Iguan/Event/Builder/Config.php) object.

[Config](../src/Iguan/Event/Builder/Config.php) can be filled in two ways:
* using `Config::fromFile` that supports two file formats: JSON and YAML.
* manually via constructor using [DotArrayAccessor](../src/Iguan/Common/Util/DotArrayAccessor.php)

Both ways works fine.

## Properties

[Builder](../src/Iguan/Event/Builder/Builder.php) operate with next properties from config.

**_common.tag_** - `string` - _[any]_ Default: `'NO TAG'` 
 
Tag application. 

**_common.type_** - `string` - _[remote]_ Default: `'remote'`
 
Event communication type. Now we support only remote communication with central server. 

**_common.auth.login_** - `string` - _[any]_ Default: `null`

Auth part: login. Must be from 1 to 255 bytes.

**_common.auth.password_** - `string` - _[any]_ Default: `null`

Auth part: password. Must be from 1 to 127 bytes.

**_common.auth.class_** - `string` - _[<? extends [CommonAuth](../src/Iguan/Event/Common/CommonAuth.php)>]_ Default: `CommonAuth::class`

Custom class for auth holding.
 
**_common.remote.payload_**format_ - `string` - _[json]_ Default: `'json'`

Define format of payload. Currently, supported by server only JSON.

**_common.remote.wait_**for_answer_ - `boolean` - _[true|false]_ Default: `true`

If `false`, each strategy will no wait for server answer. Useful for non-critical events or when you do not
want to track status of invoking server methods.

**_common.remote.client.socket.protocol_** - `string` - _[tcp|tls|ssl]_ Default: `'tcp'`

Set protocol for connections to server. `tls` and `ssl` require also setting `common.remote.client.socket.ssl_cert_path`.

**_common.remote.client.socket.host_** - `string` - _[any]_ Default: `'127.0.0.1'`

Host where event server located.

**_common.remote.client.socket.port_** - `string` - _[any]_ Default: `'11133'`

Port where event server listening for client connections.

**_common.remote.client.socket.ssl_**cert_path_ - `string` - _[any]_ Default: `''`

Path for SSL/TLS cert for using `tls` or `ssl` protocols.

**_common.remote.client.socket.timeout_**s_ - `int` - _[>=0]_ Default: `2`

Connection timeout for socket in seconds.

**_common.remote.client.socket.timeout_**ms_ - `int` - _[>=0]_ Default: `0`

Additional connection timeout for socket in milliseconds.

**_common.remote.client.socket.persist_** - `boolean` - _[true|false]_ Default: `false`

If true, socket connection can be reused between multiples application.

**_common.remote.client.socket.class_** - `string` - _[<? extends [SocketClient](../src/Iguan/Common/Remote/SocketClient.php)>]_ Default: `SocketClient::class`

Custom class for using in low-level socket communication. 

**_common.remote.client.class_** - `string` - _[<? extends [RemoteClient](../src/Iguan/Event/Common/Remote/RemoteClient.php)>]_ Default: `RemoteSocketClient::class`

Custom class for using in interaction between [RemoteCommunicateStrategy](../src/Iguan/Event/Common/Remote/RemoteCommunicateStrategy.php) (`common.remote.class`, in general)
and [SocketClient](../src/Iguan/Common/Remote/SocketClient.php) (`common.remote.client.socket.class`, in general). 

**_common.remote.class_** - `string` - _[<? extends [CommunicateStrategy](../src/Iguan/Event/Common/CommunicateStrategy.php)>]_ Default: `RemoteCommunicateStrategy::class`

Custom class for implementing client-server or local interaction. 

**_common.remote.verificator.sign.public_**key_path_ - `string` - _[any]_ Default: `''`

Path where public key for sign-verification located.

**_common.remote.verificator.class_** - `string` - _[<? extends [Verificator](../src/Iguan/Event/Subscriber/Verificator/Verificator.php)>]_ Default: `SkipVerificator::class`

Custom class for verificator implementation. If `common.remote.verificator.sign.public_key_path` presented, default will `SignVerificator::class`

**_subscriber.register_**on_subscribe_ - `boolean` - _[true|false]_ Default: `true`

If false, subscriber skip [Subject](../src/Iguan/Event/Subscriber/Subject.php) registration on subscribe. In this case, you must 
register subject by yourself. It is useful to disable when endpoint already have subscriptions from another system/app part.

**_subscriber.class_** - `string` - _[<? extends [EventSubscriber](../src/Iguan/Event/Subscriber/EventSubscriber.php)>]_ Default: `EventSubscriber::class`

Custom class for implementing own emit-subscribe system.
 
**_subscriber.guard.type_** - `string` - _[file]_ Default: `null`
 
Type for choosing strategy to prevent multiple subscriptions on each script run.

**_subscriber.guard.file.app_**version_ - `string` - _[any]_ Default: `'1.0'`
 
Current app version. File guard will revoke subscriptions when version changed. Don't forget to subscribe again.

**_subscriber.guard.file.lock_**files_location_ - `string` - _[any]_ Default: `'/tmp'`
 
Directory where file guard will keep lock files for subscriptions between script calls.

**_emitter.class_** - `string` - _[<? extends [EventEmitter](../src/Iguan/Event/Emitter/EventEmitter.php)>]_ Default: `EventEmitter::class`
 
Custom class for implementing own emit-subscribe system.

## Why not DI for classes?