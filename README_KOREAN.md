# gboxtools
안녕하세요. 웃는하루입니다.

예전에 그누보드5 기반으로 진행하던 프로젝트가 흐지부지 되어버려서
기존 소스코드를 파기하기도 아쉬웠던 관계로 오픈소스로 공개하기로 결정하였습니다.

그누보드5 기반 RSA/AES 파일암호화 모듈이며, 필요한 분들에게 도움이 되었으면 합니다.
많은 관심 부탁드립니다.

- gboxtools: https://github.com/gnh1201/gboxtools
- 기반: Gnuboard5, Laravel, RSA library, AES library, OpenSSL

## 기능소개
- RSA-AES 3-조합 암호화
그누보드5 DATA 영역의 파일을 암호화 알고리즘의 일종인, RSA-AES 3-조합 구조로 암호화 합니다.
(사용자 인터페이스에 의한 선택적 암호화 방식입니다.)
일반 RSA 또는 AES 보다 더 빠르고 안전한 방법을 제공합니다.

- 키의 생성과 파기
파일 암호화에 쓰일 키(인증서)를 생성하고 파기하기 위한 관리자를 지원합니다.

- 사용 방식
파일 암호화를 할 시 인증서를 선택한 후 인증서 암호를 입력하여 파일 암호화 및 해제를 진행합니다.
(파기된 후에는 암복호화가 불가능.)

- 파일 공유시
암호화 된 파일 공유시에는 해당 파일이 암호화되었다는 사실을 공유자에게 알리고
복호화 UI를 통해 해제를 유도할 수 있습니다.

- 외부 모듈 등록 기능
외부 모듈의 통신 고유코드와 형식을 지정할 수 있는 영역을 제공합니다.

- 커스터마이징으로 활성 가능한 기능
1. 시한형 파기 (복호화가 가능한 기간을 지정)
2. 결제모듈 연동 (결제한 사람만 키를 받아 복호화가 가능)

감사합니다.