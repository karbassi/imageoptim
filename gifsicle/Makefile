
src/gifsicle.c:
	curl http://www.lcdf.org/gifsicle/gifsicle-1.64.tar.gz | tar xz --strip-components=1 --exclude=Makefile
	patch -p1 < gifsicle-1.64-imageoptim.patch
	
clean:
	test ./src/gifsicle.c && rm -rf ./src

install:
	
